<?php
/**
 * Image Resize — Handler
 */

if (empty($_FILES['file'])) Response::error('Файл не загружен');

$width = (int)($_POST['width'] ?? 0);
$height = (int)($_POST['height'] ?? 0);
$keepAspect = ($_POST['keep_aspect'] ?? '1') === '1';

if ($width <= 0 && $height <= 0) Response::error('Укажите ширину или высоту');

$upload = FileManager::upload($_FILES['file'], ['image/jpeg', 'image/png', 'image/webp']);
if (!$upload['success']) Response::error($upload['error']);

$sourcePath = $upload['path'];
$mime = $upload['mime'];

try {
    switch ($mime) {
        case 'image/jpeg': $image = imagecreatefromjpeg($sourcePath); break;
        case 'image/png':  $image = imagecreatefrompng($sourcePath); break;
        case 'image/webp': $image = imagecreatefromwebp($sourcePath); break;
        default: throw new Exception('Неподдерживаемый формат');
    }

    $origW = imagesx($image);
    $origH = imagesy($image);

    if ($keepAspect) {
        if ($width > 0 && $height > 0) {
            $ratioW = $width / $origW;
            $ratioH = $height / $origH;
            $ratio = min($ratioW, $ratioH);
            $newW = (int)round($origW * $ratio);
            $newH = (int)round($origH * $ratio);
        } elseif ($width > 0) {
            $ratio = $width / $origW;
            $newW = $width;
            $newH = (int)round($origH * $ratio);
        } else {
            $ratio = $height / $origH;
            $newW = (int)round($origW * $ratio);
            $newH = $height;
        }
    } else {
        $newW = $width > 0 ? $width : $origW;
        $newH = $height > 0 ? $height : $origH;
    }

    $resized = imagecreatetruecolor($newW, $newH);

    if ($mime === 'image/png') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
    }

    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
    imagedestroy($image);

    $config = require __DIR__ . '/../../../config/app.php';
    $processPath = $config['process_path'];
    if (!is_dir($processPath)) mkdir($processPath, 0755, true);

    $fileId = FileManager::generateId();
    $ext = $upload['extension'];
    $outputFilename = $fileId . '.' . $ext;
    $outputPath = $processPath . '/' . $outputFilename;

    switch ($mime) {
        case 'image/jpeg': imagejpeg($resized, $outputPath, 90); break;
        case 'image/png':  imagepng($resized, $outputPath); break;
        case 'image/webp': imagewebp($resized, $outputPath, 90); break;
    }

    imagedestroy($resized);
    @unlink($sourcePath);

    return [
        'original_name' => $upload['original_name'],
        'original_dims' => "{$origW}×{$origH}",
        'new_dims'      => "{$newW}×{$newH}",
        'new_size'      => filesize($outputPath),
        'download_url'  => '/api/download?id=' . $outputFilename . '&name=' . urlencode($upload['original_name']),
        'file_name'     => $upload['original_name'],
        'file_type'     => $mime,
        'file_size'     => $upload['size'],
    ];
} catch (Exception $e) {
    @unlink($sourcePath);
    throw $e;
}
