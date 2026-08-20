<?php

$upload = $_FILES['file'] ?? null;
if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'Ошибка загрузки файла'];
}

$filename = uniqid() . '_' . time() . '.docx';
$config = require __DIR__ . '/../../../config/app.php';
$outputPath = $config['process_path'] . '/' . $filename;

$pythonScript = realpath(__DIR__ . '/../../../python/pdf2word.py');
$inputPath = escapeshellarg($upload['tmp_name']);
$outPath = escapeshellarg($outputPath);

// Execute python script
$command = "python3 " . escapeshellarg($pythonScript) . " $inputPath $outPath";
$output = shell_exec($command);

$result = json_decode($output, true);
if (!$result || !isset($result['success'])) {
    return ['error' => 'Ошибка при конвертации: ' . ($result['error'] ?? 'Неизвестная ошибка')];
}

$originalName = pathinfo($upload['name'], PATHINFO_FILENAME);

return [
    'success' => true,
    'download_url' => '/api/download?id=' . $filename . '&name=' . urlencode($originalName . '.docx'),
    'file_name' => $originalName . '.docx'
];
