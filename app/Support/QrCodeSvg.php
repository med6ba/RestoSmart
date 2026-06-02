<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeSvg
{
    public static function make(string $text, int $moduleSize = 8, int $quietZone = 4): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(max(180, $moduleSize * 32), $quietZone),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($text, 'UTF-8', ErrorCorrectionLevel::M());
    }
}
