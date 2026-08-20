<?php

$upload = $_FILES['file'] ?? null;
if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'Ошибка загрузки файла'];
}
$password = $_POST['password'] ?? '';
if (empty($password)) return ['error' => 'Пароль не указан'];

$filename = uniqid() . '_' . time() . '.pdf';
$config = require __DIR__ . '/../../../config/app.php';
$outputPath = $config['process_path'] . '/' . $filename;

$pythonScript = realpath(__DIR__ . '/../../../python/pdf_password.py');
$inputPath = escapeshellarg($upload['tmp_name']);
$outPath = escapeshellarg($outputPath);
$pwdArg = escapeshellarg($password);

// Execute python script
$command = "python3 " . escapeshellarg($pythonScript) . " $inputPath $outPath $pwdArg";
$output = shell_exec($command);

$result = json_decode($output, true);
if (!$result || !isset($result['success'])) {
    return ['error' => 'Ошибка при установке пароля: ' . ($result['error'] ?? 'Неизвестная ошибка')];
}

$originalName = pathinfo($upload['name'], PATHINFO_FILENAME);

return [
    'success' => true,
    'download_url' => '/api/download?id=' . $filename . '&name=' . urlencode($originalName . '_protected.pdf'),
    'file_name' => $originalName . '_protected.pdf'
];
