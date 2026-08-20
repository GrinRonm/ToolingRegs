<?php
/**
 * Image Compress — Handler
 * Compresses uploaded images using GD library
 */

if (empty($_FILES['file'])) {
    Response::error('Файл не загружен');
}

$quality = isset($_POST['quality']) ? (int) $_POST['quality'] : 80;
$quality = max(10, min(100, $quality));

// Upload file
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
$upload = FileManager::upload($_FILES['file'], $allowedMimes);

if (!$upload['success']) {
    Response::error($upload['error']);
}

$sourcePath = $upload['path'];
$originalSize = $upload['size'];
$mime = $upload['mime'];
$originalName = $upload['original_name'];

try {
    // Load image with GD
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($sourcePath);
            break;
        default:
            throw new Exception('Неподдерживаемый формат');
    }

    if (!$image) {
        throw new Exception('Не удалось открыть изображение');
    }

    // Preserve transparency for PNG
    if ($mime === 'image/png') {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    // Save compressed to temp file
    $config = require __DIR__ . '/../../../config/app.php';
    $processPath = $config['process_path'];
    if (!is_dir($processPath)) {
        mkdir($processPath, 0755, true);
    }

    $fileId = FileManager::generateId();
    $ext = $upload['extension'];
    $outputFilename = $fileId . '.' . $ext;
    $outputPath = $processPath . '/' . $outputFilename;

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($image, $outputPath, $quality);
            break;
        case 'image/png':
            // PNG quality: 0 (no compression) to 9 (max compression)
            $pngQuality = (int) round((100 - $quality) / 100 * 9);
            imagepng($image, $outputPath, $pngQuality);
            break;
        case 'image/webp':
            imagewebp($image, $outputPath, $quality);
            break;
    }

    imagedestroy($image);

    $compressedSize = filesize($outputPath);

    // Clean up uploaded file
    @unlink($sourcePath);

    return [
        'original_name'   => $originalName,
        'original_size'   => $originalSize,
        'compressed_size'  => $compressedSize,
        'quality'          => $quality,
        'download_url'     => '/api/download?id=' . $outputFilename . '&name=' . urlencode($originalName),
        'file_name'        => $originalName,
        'file_type'        => $mime,
        'file_size'        => $originalSize,
    ];

} catch (Exception $e) {
    @unlink($sourcePath);
    throw $e;
}
