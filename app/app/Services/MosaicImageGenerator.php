<?php

namespace App\Services;

use RuntimeException;

class MosaicImageGenerator
{
    /**
     * @param  list<string>  $imageUrls
     */
    public function generate(array $imageUrls, int $grid = 3, int $size = 1080): string
    {
        $grid = max(2, min(4, $grid));
        $needed = $grid * $grid;
        $imageUrls = array_values(array_slice($imageUrls, 0, $needed));

        if (count($imageUrls) < $needed) {
            throw new RuntimeException("Selecione {$needed} capas para a grade {$grid}x{$grid}.");
        }

        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('Extensão GD indisponível.');
        }

        $canvas = imagecreatetruecolor($size, $size);
        $gap = 8;
        $cell = (int) floor(($size - ($gap * ($grid + 1))) / $grid);
        $bg = imagecolorallocate($canvas, 7, 10, 18);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $bg);

        foreach ($imageUrls as $index => $url) {
            $tile = $this->loadImage($url);

            if (! $tile) {
                continue;
            }

            $col = $index % $grid;
            $row = (int) floor($index / $grid);
            $x = $gap + ($col * ($cell + $gap));
            $y = $gap + ($row * ($cell + $gap));

            $srcW = imagesx($tile);
            $srcH = imagesy($tile);
            $side = min($srcW, $srcH);
            $sx = (int) (($srcW - $side) / 2);
            $sy = (int) (($srcH - $side) / 2);

            imagecopyresampled($canvas, $tile, $x, $y, $sx, $sy, $cell, $cell, $side, $side);
            imagedestroy($tile);
        }

        $dir = storage_path('app/public/mosaics');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = 'mosaic-'.uniqid('', true).'.png';
        $path = $dir.DIRECTORY_SEPARATOR.$filename;

        if (! imagepng($canvas, $path, 6)) {
            imagedestroy($canvas);
            throw new RuntimeException('Falha ao salvar o mosaico.');
        }

        imagedestroy($canvas);

        return 'mosaics/'.$filename;
    }

    /**
     * @return \GdImage|false
     */
    private function loadImage(string $url)
    {
        $binary = @file_get_contents($url);

        if ($binary === false) {
            return false;
        }

        return @imagecreatefromstring($binary);
    }
}
