<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

class QrCodePng
{
    public static function make(string $text, int $size = 512, int $quietZone = 4): string
    {
        $renderer = new GDLibRenderer($size, $quietZone, 'png', 9);

        return (new Writer($renderer))->writeString($text, 'UTF-8', ErrorCorrectionLevel::M());
    }
}
