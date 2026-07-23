<?php

declare(strict_types=1);

function createIcon(string $path, int $size, bool $maskable = false): void
{
    $image = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($image, 255, 255, 255);

    for ($x = 0; $x < $size; $x++) {
        $ratio = $x / max(1, $size - 1);
        $color = imagecolorallocate($image, 255, (int) round(144 - (43 * $ratio)), (int) round(100 - (35 * $ratio)));
        imageline($image, $x, 0, $x, $size, $color);
    }

    $safe = $maskable ? 0.24 : 0.18;
    $left = (int) round($size * $safe);
    $right = $size - $left;
    $top = (int) round($size * 0.31);
    $bottom = (int) round($size * 0.70);
    $stroke = max(8, (int) round($size * 0.095));
    $middle = (int) round($size / 2);

    imagesetthickness($image, $stroke);
    imageline($image, $left, $bottom, $left, $top, $white);
    imageline($image, $left, $top, $middle, $bottom, $white);
    imageline($image, $middle, $bottom, $right, $top, $white);
    imageline($image, $right, $top, $right, $bottom, $white);

    imagepng($image, $path, 9);
    imagedestroy($image);
}

$directory = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'icons';
createIcon($directory.DIRECTORY_SEPARATOR.'pwa-192.png', 192);
createIcon($directory.DIRECTORY_SEPARATOR.'pwa-512.png', 512);
createIcon($directory.DIRECTORY_SEPARATOR.'pwa-maskable-512.png', 512, true);
createIcon($directory.DIRECTORY_SEPARATOR.'apple-touch-icon.png', 180);
