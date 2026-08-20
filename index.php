<?php
/**
 * Instyment — Entry Point & Router
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Load core
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/ToolRegistry.php';
require_once __DIR__ . '/core/FileManager.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Response.php';

// Initialize
Security::init();

$config = require __DIR__ . '/config/app.php';

// Parse request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// ========== API Routes ==========

// GET /api/tools — list all tools
if ($uri === '/api/tools' && $method === 'GET') {
    $tools = ToolRegistry::getAll();
    $result = [];
    foreach ($tools as $tool) {
        $result[] = [
            'id'          => $tool['id'],
            'name'        => $tool['name'],
            'description' => $tool['description'],
            'category'    => $tool['category'],
            'icon'        => $tool['icon'] ?? '🔧',
            'tags'        => $tool['tags'] ?? [],
            'url'         => $tool['_url'],
        ];
    }
    Response::json(['tools' => $result]);
}

// GET /api/tools/search?q=
if ($uri === '/api/tools/search' && $method === 'GET') {
    $q = $_GET['q'] ?? '';
    $tools = ToolRegistry::search($q);
    $result = [];
    foreach ($tools as $tool) {
        $result[] = [
            'id'          => $tool['id'],
            'name'        => $tool['name'],
            'description' => $tool['description'],
            'category'    => $tool['category'],
            'icon'        => $tool['icon'] ?? '🔧',
            'tags'        => $tool['tags'] ?? [],
            'url'         => $tool['_url'],
        ];
    }
    Response::json(['tools' => $result, 'query' => $q]);
}

// GET /api/csrf — get CSRF token
if ($uri === '/api/csrf' && $method === 'GET') {
    Response::json(['token' => Security::getToken()]);
}

// POST /api/tool/{id}/process — process with a tool
if (preg_match('#^/api/tool/([a-z0-9\-]+)/process$#', $uri, $m) && $method === 'POST') {
    $toolId = $m[1];
    $tool = ToolRegistry::get($toolId);

    if (!$tool) {
        Response::error('Инструмент не найден', 404);
    }

    // Verify CSRF
    if (!Security::verifyCsrf()) {
        Response::error('Недействительный токен безопасности', 403);
    }

    // Include tool handler
    $handlerPath = $tool['_path'] . '/handler.php';
    if (!file_exists($handlerPath)) {
        Response::error('Обработчик не найден', 500);
    }

    $startTime = microtime(true);

    try {
        // Handler should return array with result
        $result = require $handlerPath;

        $duration = round((microtime(true) - $startTime) * 1000);

        // Log action
        Logger::log([
            'tool_id'     => $toolId,
            'action'      => 'process',
            'file_name'   => $result['file_name'] ?? null,
            'file_type'   => $result['file_type'] ?? null,
            'file_size'   => $result['file_size'] ?? null,
            'result'      => 'success',
            'duration_ms' => $duration,
        ]);

        Logger::recordStat($toolId);

        if (is_array($result)) {
            Response::json($result);
        }
    } catch (Exception $e) {
        $duration = round((microtime(true) - $startTime) * 1000);

        Logger::log([
            'tool_id'     => $toolId,
            'action'      => 'process',
            'result'      => 'error',
            'error'       => $e->getMessage(),
            'duration_ms' => $duration,
        ]);

        error_log("Tool {$toolId} error: " . $e->getMessage());
        Response::error('Не удалось обработать файл. Попробуйте ещё раз.', 500);
    }
}

// GET /api/download?id={filename}
if ($uri === '/api/download' && $method === 'GET') {
    $filename = basename($_GET['id'] ?? '');
    if (!$filename) {
        Response::error('Идентификатор файла не указан', 400);
    }
    
    $path = FileManager::getProcessedPath($filename);

    if (!$path) {
        Response::error('Файл не найден или истёк', 404);
    }

    $downloadName = $_GET['name'] ?? $filename;
    Response::download($path, $downloadName);
}

// POST /api/download-zip
if ($uri === '/api/download-zip' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['files'])) {
        Response::error('Нет файлов для архивации', 400);
    }

    $zipName = 'instyment_' . time() . '.zip';
    $config = require __DIR__ . '/config/app.php';
    $zipPath = $config['process_path'] . '/' . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        Response::error('Не удалось создать архив', 500);
    }

    foreach ($data['files'] as $f) {
        if (empty($f['id']) || empty($f['name'])) continue;
        
        $filePath = FileManager::getProcessedPath(basename($f['id']));
        if ($filePath && file_exists($filePath)) {
            $zip->addFile($filePath, basename($f['name']));
        }
    }

    $zip->close();
    
    Response::json([
        'download_url' => '/api/download?id=' . $zipName . '&name=' . urlencode('Архив_Instyment.zip')
    ]);
}

// GET /api/job/{id} — check job status
if (preg_match('#^/api/job/([a-z0-9\-_]+)$#i', $uri, $m) && $method === 'GET') {
    $jobId = $m[1];
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM jobs WHERE id = :id");
    $stmt->execute([':id' => $jobId]);
    $job = $stmt->fetch();

    if (!$job) {
        Response::error('Задача не найдена', 404);
    }

    $result = [
        'job_id'   => $job['id'],
        'status'   => $job['status'],
        'progress' => (int) $job['progress'],
    ];

    if ($job['status'] === 'completed' && $job['result']) {
        $result['result'] = json_decode($job['result'], true);
    }
    if ($job['status'] === 'failed') {
        $result['error'] = 'Ошибка обработки';
    }

    Response::json($result);
}


// ========== Page Routes ==========

// Tool page: /tool/{category}/{tool-dir}
if (preg_match('#^/tool/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $uri, $m)) {
    $categoryDir = $m[1];
    $toolDir = $m[2];

    // Find tool by directory
    $currentTool = null;
    foreach (ToolRegistry::getAll() as $tool) {
        if ($tool['_category_dir'] === $categoryDir && $tool['_tool_dir'] === $toolDir) {
            $currentTool = $tool;
            break;
        }
    }

    if (!$currentTool) {
        Response::notFound();
    }

    // Log page view
    Logger::log([
        'tool_id' => $currentTool['id'],
        'action'  => 'view',
    ]);

    $pageTitle = $currentTool['name'] . ' — Instyment';
    $pageDescription = $currentTool['description'];
    $viewPath = $currentTool['_path'] . '/view.php';

    require __DIR__ . '/templates/layout.php';
    exit;
}

// Home page
if ($uri === '/') {
    $tools = ToolRegistry::getAll();
    $categories = ToolRegistry::getCategories();
    $pageTitle = 'Instyment — Онлайн инструменты';
    $pageDescription = 'Бесплатные онлайн инструменты для работы с изображениями, PDF, текстом и другими файлами';
    $currentTool = null;

    Logger::log(['action' => 'home_view']);

    require __DIR__ . '/templates/layout.php';
    exit;
}

// Static files should be served by nginx, but just in case
Response::notFound();
