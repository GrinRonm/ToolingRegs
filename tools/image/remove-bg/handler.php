<?php

$upload = $_FILES['file'] ?? null;
if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'Ошибка загрузки файла'];
}

$info = getimagesize($upload['tmp_name']);
if (!$info) return ['error' => 'Неверный формат изображения'];

$filename = uniqid() . '_' . time() . '.png';
$config = require __DIR__ . '/../../../config/app.php';
$outputPath = $config['process_path'] . '/' . $filename;

$pythonScript = realpath(__DIR__ . '/../../../python/rembg_handler.py');
$inputPath = escapeshellarg($upload['tmp_name']);
$outPath = escapeshellarg($outputPath);

// Execute python script
$command = "export U2NET_HOME=/tmp/.u2net; python3 " . escapeshellarg($pythonScript) . " $inputPath $outPath";
$output = shell_exec($command);

$result = json_decode($output, true);
if (!$result || !isset($result['success'])) {
    return ['error' => 'Ошибка при удалении фона: ' . ($result['error'] ?? 'Неизвестная ошибка')];
}

$originalName = pathinfo($upload['name'], PATHINFO_FILENAME);

return [
    'download_url' => '/api/download?id=' . $filename . '&name=' . urlencode($originalName . '_nobg.png'),
    'file_name' => $originalName . '_nobg.png'
];
