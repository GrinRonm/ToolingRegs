<?php
/**
 * Image Convert — Handler
 * Converts images between formats using GD
 */

if (empty($_FILES['file'])) {
    Response::error('Файл не загружен');
}

$targetFormat = $_POST['format'] ?? 'png';
$quality = isset($_POST['quality']) ? (int) $_POST['quality'] : 90;
$quality = max(10, min(100, $quality));

$allowedFormats = ['jpg', 'png', 'webp', 'gif', 'bmp'];
if (!in_array($targetFormat, $allowedFormats)) {
    Response::error('Неподдерживаемый формат: ' . $targetFormat);
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'];
$upload = FileManager::upload($_FILES['file'], $allowedMimes);

if (!$upload['success']) {
    Response::error($upload['error']);
}

$sourcePath = $upload['path'];
$mime = $upload['mime'];
$originalName = $upload['original_name'];

try {
    // Load source image
    switch ($mime) {
        case 'image/jpeg': $image = imagecreatefromjpeg($sourcePath); break;
        case 'image/png':  $image = imagecreatefrompng($sourcePath); break;
        case 'image/webp': $image = imagecreatefromwebp($sourcePath); break;
        case 'image/gif':  $image = imagecreatefromgif($sourcePath); break;
        case 'image/bmp':  $image = imagecreatefrombmp($sourcePath); break;
        default: throw new Exception('Неподдерживаемый исходный формат');
    }

    if (!$image) {
        throw new Exception('Не удалось открыть изображение');
    }

    // Handle transparency
    if (in_array($targetFormat, ['png', 'webp', 'gif'])) {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    } else {
        // For formats without transparency (jpg, bmp), add white background
        $width = imagesx($image);
        $height = imagesy($image);
        $newImage = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $white);
        imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);
        $image = $newImage;
    }

    // Save in target format
    $config = require __DIR__ . '/../../../config/app.php';
    $processPath = $config['process_path'];
    if (!is_dir($processPath)) mkdir($processPath, 0755, true);

    $fileId = FileManager::generateId();
    $outputFilename = $fileId . '.' . $targetFormat;
    $outputPath = $processPath . '/' . $outputFilename;

    switch ($targetFormat) {
        case 'jpg':  imagejpeg($image, $outputPath, $quality); break;
        case 'png':  imagepng($image, $outputPath, (int)round((100 - $quality) / 100 * 9)); break;
        case 'webp': imagewebp($image, $outputPath, $quality); break;
        case 'gif':  imagegif($image, $outputPath); break;
        case 'bmp':  imagebmp($image, $outputPath); break;
    }

    imagedestroy($image);

    $newSize = filesize($outputPath);

    // Generate download name
    $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
    $downloadName = $nameWithoutExt . '.' . $targetFormat;

    @unlink($sourcePath);

    return [
        'original_name'  => $originalName,
        'target_format'  => $targetFormat,
        'new_size'       => $newSize,
        'download_url'   => '/api/download?id=' . $outputFilename . '&name=' . urlencode($downloadName),
        'file_name'      => $originalName,
        'file_type'      => $mime,
        'file_size'      => $upload['size'],
    ];

} catch (Exception $e) {
    @unlink($sourcePath);
    throw $e;
}
