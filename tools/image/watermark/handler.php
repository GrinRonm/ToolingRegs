<?php

$upload = $_FILES['file'] ?? null;
if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'Ошибка загрузки файла'];
}

$text = $_POST['text'] ?? 'Watermark';
$colorHex = $_POST['color'] ?? '#ffffff';
$size = (int)($_POST['size'] ?? 50);

$info = getimagesize($upload['tmp_name']);
if (!$info) return ['error' => 'Неверный формат изображения'];

$mime = $info['mime'];
$image = null;
if ($mime === 'image/jpeg') $image = @imagecreatefromjpeg($upload['tmp_name']);
elseif ($mime === 'image/png') $image = @imagecreatefrompng($upload['tmp_name']);
elseif ($mime === 'image/webp') $image = @imagecreatefromwebp($upload['tmp_name']);

if (!$image) return ['error' => 'Не удалось обработать изображение'];

// Convert hex to RGB
$colorHex = ltrim($colorHex, '#');
if (strlen($colorHex) == 3) {
    $r = hexdec(str_repeat(substr($colorHex, 0, 1), 2));
    $g = hexdec(str_repeat(substr($colorHex, 1, 1), 2));
    $b = hexdec(str_repeat(substr($colorHex, 2, 1), 2));
} else {
    $r = hexdec(substr($colorHex, 0, 2));
    $g = hexdec(substr($colorHex, 2, 2));
    $b = hexdec(substr($colorHex, 4, 2));
}

// Enable alpha blending
imagealphablending($image, true);
imagesavealpha($image, true);

$textColor = imagecolorallocatealpha($image, $r, $g, $b, 50); // 50 is alpha (0-127)

// Add text (using built-in font for simplicity)
$width = imagesx($image);
$height = imagesy($image);

// A simple way to draw text without TTF font (if no TTF is available)
// But imagestring is very small. Let's try imagettftext if font exists.
$fontPath = __DIR__ . '/arial.ttf';
if (file_exists($fontPath)) {
    // calculate bounding box
    $bbox = @imagettfbbox($size, 0, $fontPath, $text);
    if (is_array($bbox)) {
        $textW = $bbox[2] - $bbox[0];
        $textH = $bbox[1] - $bbox[7];
        // Bottom right
        $x = $width - $textW - 20;
        $y = $height - 20;
        @imagettftext($image, $size, 0, $x, $y, $textColor, $fontPath, $text);
    } else {
        // Fallback to imagestring
        imagestring($image, 5, $width - strlen($text)*9 - 10, $height - 20, $text, $textColor);
    }
} else {
    // Fallback to imagestring
    imagestring($image, 5, $width - strlen($text)*9 - 10, $height - 20, $text, $textColor);
}

$outputFilename = uniqid() . '_' . time() . '.png';
$config = require __DIR__ . '/../../../config/app.php';
$outputPath = $config['process_path'] . '/' . $outputFilename;

imagepng($image, $outputPath);
imagedestroy($image);

return [
    'download_url' => '/api/download?id=' . $outputFilename . '&name=' . urlencode('watermark_' . $upload['name']),
    'file_name' => 'watermark_' . $upload['name']
];
