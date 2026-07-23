<?php

declare(strict_types=1);

function createIcon(string $sourcePath, string $path, int $size, bool $maskable = false, bool $rounded = false): void
{
    $source = imagecreatefrompng($sourcePath);
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);

    $padding = $maskable ? (int) round($size * .1) : 0;
    $targetSize = $size - ($padding * 2);
    imagecopyresampled(
        $canvas,
        $source,
        $padding,
        $padding,
        0,
        0,
        $targetSize,
        $targetSize,
        imagesx($source),
        imagesy($source),
    );

    if ($rounded) {
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        $radius = (int) round($size * .22);

        for ($y = 0; $y < $radius; $y++) {
            for ($x = 0; $x < $radius; $x++) {
                $distance = (($radius - $x) ** 2) + (($radius - $y) ** 2);
                if ($distance > $radius ** 2) {
                    imagesetpixel($canvas, $x, $y, $transparent);
                    imagesetpixel($canvas, $size - 1 - $x, $y, $transparent);
                    imagesetpixel($canvas, $x, $size - 1 - $y, $transparent);
                    imagesetpixel($canvas, $size - 1 - $x, $size - 1 - $y, $transparent);
                }
            }
        }
    }

    imagepng($canvas, $path, 9);
    imagedestroy($source);
    imagedestroy($canvas);
}

$directory = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'icons';
$source = dirname(__DIR__).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'myokucare-logo.png';
createIcon($source, $directory.DIRECTORY_SEPARATOR.'pwa-192.png', 192);
createIcon($source, $directory.DIRECTORY_SEPARATOR.'pwa-512.png', 512);
createIcon($source, $directory.DIRECTORY_SEPARATOR.'pwa-maskable-512.png', 512, true);
createIcon($source, $directory.DIRECTORY_SEPARATOR.'apple-touch-icon.png', 180);
createIcon($source, $directory.DIRECTORY_SEPARATOR.'favicon-64.png', 64, false, true);
