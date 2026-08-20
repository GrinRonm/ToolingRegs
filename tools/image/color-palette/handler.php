<?php

$upload = $_FILES['file'] ?? null;
if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'Ошибка загрузки файла'];
}

$info = getimagesize($upload['tmp_name']);
if (!$info) return ['error' => 'Неверный формат изображения'];

$mime = $info['mime'];
$image = null;
if ($mime === 'image/jpeg') $image = @imagecreatefromjpeg($upload['tmp_name']);
elseif ($mime === 'image/png') $image = @imagecreatefrompng($upload['tmp_name']);
elseif ($mime === 'image/webp') $image = @imagecreatefromwebp($upload['tmp_name']);

if (!$image) return ['error' => 'Не удалось обработать изображение'];

// Scale down to 50x50 to speed up and average colors
$small = imagecreatetruecolor(50, 50);
imagecopyresampled($small, $image, 0, 0, 0, 0, 50, 50, imagesx($image), imagesy($image));
imagedestroy($image);

$colors = [];
for ($x = 0; $x < 50; $x++) {
    for ($y = 0; $y < 50; $y++) {
        $rgb = imagecolorat($small, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        // Quantize colors (round to nearest 16 to group similar colors)
        $r = round($r / 16) * 16;
        $g = round($g / 16) * 16;
        $b = round($b / 16) * 16;
        $hex = sprintf("#%02x%02x%02x", min(255, $r), min(255, $g), min(255, $b));
        if (!isset($colors[$hex])) $colors[$hex] = 0;
        $colors[$hex]++;
    }
}
imagedestroy($small);

arsort($colors);
$topColors = array_slice(array_keys($colors), 0, 5);

return [
    'colors' => $topColors
];
