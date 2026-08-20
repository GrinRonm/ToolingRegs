<?php
/**
 * PDF Merge — Handler
 * Merges multiple PDF files using Python/PyPDF2
 */

if (empty($_FILES['files'])) {
    Response::error('Файлы не загружены');
}

$uploads = FileManager::uploadMultiple($_FILES['files'], ['application/pdf']);
$paths = [];

foreach ($uploads as $upload) {
    if (!$upload['success']) {
        // Cleanup already uploaded
        foreach ($paths as $p) @unlink($p);
        Response::error($upload['error']);
    }
    $paths[] = $upload['path'];
}

if (count($paths) < 2) {
    foreach ($paths as $p) @unlink($p);
    Response::error('Нужно минимум 2 PDF');
}

try {
    $config = require __DIR__ . '/../../../config/app.php';
    $processPath = $config['process_path'];
    if (!is_dir($processPath)) mkdir($processPath, 0755, true);

    $fileId = FileManager::generateId();
    $outputFilename = $fileId . '.pdf';
    $outputPath = $processPath . '/' . $outputFilename;

    // Build input JSON for Python
    $inputData = json_encode(['action' => 'merge', 'files' => $paths, 'output' => $outputPath]);
    $tempInput = tempnam($config['temp_path'] ?: sys_get_temp_dir(), 'pdf_');
    file_put_contents($tempInput, $inputData);

    $cmd = escapeshellcmd($config['python_bin']) . ' '
         . escapeshellarg($config['python_path'] . '/pdf_handler.py') . ' '
         . escapeshellarg($tempInput);

    $output = shell_exec($cmd . ' 2>&1');
    @unlink($tempInput);

    $result = json_decode($output, true);

    // Cleanup uploaded files
    foreach ($paths as $p) @unlink($p);

    if (!$result || !$result['success']) {
        throw new Exception($result['error'] ?? 'Ошибка обработки PDF');
    }

    return [
        'size'         => filesize($outputPath),
        'pages'        => $result['pages'] ?? 0,
        'download_url' => '/api/download?id=' . $outputFilename . '&name=merged.pdf',
        'file_name'    => 'merged.pdf',
        'file_type'    => 'application/pdf',
        'file_size'    => filesize($outputPath),
    ];

} catch (Exception $e) {
    foreach ($paths as $p) @unlink($p);
    throw $e;
}
