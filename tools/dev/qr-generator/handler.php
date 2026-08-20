<?php
/**
 * QR Generator — Handler
 * Generates QR code using Python
 */

$text = $_POST['text'] ?? '';
if (empty($text)) {
    Response::error('Пустой текст');
}

$size = (int)($_POST['size'] ?? 300);
$color = $_POST['color'] ?? '#000000';

$config = require __DIR__ . '/../../../config/app.php';
$processPath = $config['process_path'];
if (!is_dir($processPath)) {
    mkdir($processPath, 0755, true);
}

$fileId = FileManager::generateId();
$outputFilename = $fileId . '.png';
$outputPath = $processPath . '/' . $outputFilename;

$inputData = json_encode([
    'text' => $text,
    'size' => $size,
    'color' => $color,
    'output' => $outputPath,
]);

$tempInput = tempnam($config['temp_path'] ?: sys_get_temp_dir(), 'qr_');
file_put_contents($tempInput, $inputData);

$cmd = escapeshellcmd($config['python_bin']) . ' '
     . escapeshellarg($config['python_path'] . '/qr_handler.py') . ' '
     . escapeshellarg($tempInput);

$output = shell_exec($cmd . ' 2>&1');
@unlink($tempInput);

$result = json_decode($output, true);

if (!$result || !$result['success']) {
    throw new Exception($result['error'] ?? 'Ошибка генерации QR-кода');
}

return [
    'url' => '/storage/processed/' . $outputFilename,
    'download_url' => '/api/download?id=' . $outputFilename . '&name=qr-code.png',
];
