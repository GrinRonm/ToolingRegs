<?php
/**
 * OCR + Translate — Handler
 * Two actions:
 *   1. OCR: upload image → extract text via Python/Tesseract
 *   2. Translate: text → translated text via Python/deep-translator
 */

$config = require __DIR__ . '/../../../config/app.php';
$action = $_POST['action'] ?? 'ocr';

if ($action === 'translate') {
    // ── Translation Mode ──
    $text = $_POST['text'] ?? '';
    $sourceLang = $_POST['source'] ?? 'en';
    $targetLang = $_POST['target'] ?? 'ru';

    if (empty(trim($text))) {
        Response::error('Текст для перевода пуст');
    }

    if (mb_strlen($text) > 10000) {
        Response::error('Текст слишком длинный (макс. 10000 символов)');
    }

    // Sanitize language codes
    $allowedLangs = ['en', 'ru', 'de', 'fr', 'es', 'zh-CN', 'ja', 'ko', 'auto'];
    if (!in_array($sourceLang, $allowedLangs) || !in_array($targetLang, $allowedLangs)) {
        Response::error('Неподдерживаемый язык');
    }

    // Call Python translator
    $pythonPath = $config['python_path'];
    $tempInput = tempnam($config['temp_path'] ?: sys_get_temp_dir(), 'translate_');
    file_put_contents($tempInput, json_encode([
        'text'   => $text,
        'source' => $sourceLang,
        'target' => $targetLang,
    ]));

    $cmd = escapeshellcmd($config['python_bin']) . ' '
         . escapeshellarg($pythonPath . '/translate_handler.py') . ' '
         . escapeshellarg($tempInput);

    $output = shell_exec($cmd . ' 2>&1');
    @unlink($tempInput);

    $result = json_decode($output, true);

    if (!$result || !isset($result['success']) || !$result['success']) {
        $error = $result['error'] ?? 'Ошибка перевода';
        throw new Exception($error);
    }

    return [
        'translated_text' => $result['text'],
        'source_lang'     => $sourceLang,
        'target_lang'     => $targetLang,
        'file_name'       => null,
    ];

} else {
    // ── OCR Mode ──
    if (empty($_FILES['file'])) {
        Response::error('Файл не загружен');
    }

    $lang = $_POST['lang'] ?? 'eng+rus';
    $allowedLangs = ['eng', 'rus', 'eng+rus'];
    if (!in_array($lang, $allowedLangs)) {
        $lang = 'eng+rus';
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'];
    $upload = FileManager::upload($_FILES['file'], $allowedMimes);

    if (!$upload['success']) {
        Response::error($upload['error']);
    }

    $sourcePath = $upload['path'];

    try {
        // Call Python OCR
        $pythonPath = $config['python_path'];
        $cmd = escapeshellcmd($config['python_bin']) . ' '
             . escapeshellarg($pythonPath . '/ocr_handler.py') . ' '
             . escapeshellarg($sourcePath) . ' '
             . escapeshellarg($lang);

        $output = shell_exec($cmd . ' 2>&1');
        $result = json_decode($output, true);

        @unlink($sourcePath);

        if (!$result || !isset($result['success']) || !$result['success']) {
            $error = $result['error'] ?? 'Ошибка OCR';
            throw new Exception($error);
        }

        return [
            'text'      => $result['text'],
            'file_name' => $upload['original_name'],
            'file_type' => $upload['mime'],
            'file_size' => $upload['size'],
        ];

    } catch (Exception $e) {
        @unlink($sourcePath);
        throw $e;
    }
}
