<?php
/**
 * PDF Split — Handler
 */

if (empty($_FILES['file'])) Response::error('Файл не загружен');

$upload = FileManager::upload($_FILES['file'], ['application/pdf']);
if (!$upload['success']) Response::error($upload['error']);

try {
    $config = require __DIR__ . '/../../../config/app.php';
    $processPath = $config['process_path'];
    if (!is_dir($processPath)) mkdir($processPath, 0755, true);

    $inputData = json_encode([
        'action'     => 'split',
        'file'       => $upload['path'],
        'output_dir' => $processPath,
    ]);
    $tempInput = tempnam($config['temp_path'] ?: sys_get_temp_dir(), 'pdf_');
    file_put_contents($tempInput, $inputData);

    $cmd = escapeshellcmd($config['python_bin']) . ' '
         . escapeshellarg($config['python_path'] . '/pdf_handler.py') . ' '
         . escapeshellarg($tempInput);

    $output = shell_exec($cmd . ' 2>&1');
    @unlink($tempInput);
    @unlink($upload['path']);

    $result = json_decode($output, true);

    if (!$result || !$result['success']) {
        throw new Exception($result['error'] ?? 'Ошибка разделения PDF');
    }

    // Build download URLs
    $files = [];
    foreach ($result['files'] as $f) {
        $filename = basename($f['path']);
        $files[] = [
            'page'         => $f['page'],
            'size'         => $f['size'],
            'download_url' => '/api/download?id=' . $filename . '&name=' . urlencode($f['filename']),
        ];
    }

    return [
        'total_pages' => $result['total_pages'],
        'files'       => $files,
        'file_name'   => $upload['original_name'],
        'file_type'   => 'application/pdf',
        'file_size'   => $upload['size'],
    ];

} catch (Exception $e) {
    @unlink($upload['path']);
    throw $e;
}
