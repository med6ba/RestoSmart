<?php

namespace App\Support;

use InvalidArgumentException;

class QrCodeSvg
{
    private const VERSION = 2;
    private const SIZE = 25;
    private const DATA_CODEWORDS = 34;
    private const ECC_CODEWORDS = 10;

    /**
     * Generate a compact SVG QR code for short table tokens.
     */
    public static function make(string $text, int $moduleSize = 8, int $quietZone = 4): string
    {
        if (strlen($text) > 32) {
            throw new InvalidArgumentException('QR payload is too long for this generator.');
        }

        $codewords = array_merge(
            self::dataCodewords($text),
            self::reedSolomonRemainder(self::dataCodewords($text), self::ECC_CODEWORDS),
        );

        $matrix = new self;
        $matrix->drawFunctionPatterns();
        $matrix->drawCodewords($codewords);
        $matrix->drawFormatBits(0);

        return $matrix->toSvg($moduleSize, $quietZone);
    }

    /**
     * @var array<int, array<int, bool|null>>
     */
    private array $modules = [];

    /**
     * @var array<int, array<int, bool>>
     */
    private array $reserved = [];

    private function __construct()
    {
        for ($y = 0; $y < self::SIZE; $y++) {
            $this->modules[$y] = array_fill(0, self::SIZE, null);
            $this->reserved[$y] = array_fill(0, self::SIZE, false);
        }
    }

    /**
     * @return array<int, int>
     */
    private static function dataCodewords(string $text): array
    {
        $bits = [];
        $append = static function (int $value, int $length) use (&$bits): void {
            for ($i = $length - 1; $i >= 0; $i--) {
                $bits[] = ($value >> $i) & 1;
            }
        };

        $append(0b0100, 4);
        $append(strlen($text), 8);

        foreach (array_values(unpack('C*', $text)) as $byte) {
            $append($byte, 8);
        }

        $capacityBits = self::DATA_CODEWORDS * 8;
        $append(0, min(4, $capacityBits - count($bits)));

        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $codewords = [];
        foreach (array_chunk($bits, 8) as $chunk) {
            $byte = 0;
            foreach ($chunk as $bit) {
                $byte = ($byte << 1) | $bit;
            }
            $codewords[] = $byte;
        }

        for ($i = 0; count($codewords) < self::DATA_CODEWORDS; $i++) {
            $codewords[] = $i % 2 === 0 ? 0xEC : 0x11;
        }

        return $codewords;
    }

    /**
     * @param  array<int, int>  $data
     * @return array<int, int>
     */
    private static function reedSolomonRemainder(array $data, int $degree): array
    {
        $divisor = self::reedSolomonDivisor($degree);
        $result = array_fill(0, $degree, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($result);
            $result[] = 0;

            for ($i = 0; $i < $degree; $i++) {
                $result[$i] ^= self::gfMultiply($divisor[$i], $factor);
            }
        }

        return $result;
    }

    /**
     * @return array<int, int>
     */
    private static function reedSolomonDivisor(int $degree): array
    {
        $result = array_fill(0, $degree, 0);
        $result[$degree - 1] = 1;
        $root = 1;

        for ($i = 0; $i < $degree; $i++) {
            for ($j = 0; $j < $degree; $j++) {
                $result[$j] = self::gfMultiply($result[$j], $root);

                if ($j + 1 < $degree) {
                    $result[$j] ^= $result[$j + 1];
                }
            }

            $root = self::gfMultiply($root, 0x02);
        }

        return $result;
    }

    private static function gfMultiply(int $x, int $y): int
    {
        $product = 0;

        for ($i = 7; $i >= 0; $i--) {
            $product = ($product << 1) ^ (($product >> 7) * 0x11D);
            $product &= 0xFF;

            if ((($y >> $i) & 1) !== 0) {
                $product ^= $x;
            }
        }

        return $product;
    }

    private function drawFunctionPatterns(): void
    {
        $this->drawFinderPattern(3, 3);
        $this->drawFinderPattern(self::SIZE - 4, 3);
        $this->drawFinderPattern(3, self::SIZE - 4);
        $this->drawAlignmentPattern(18, 18);

        for ($i = 0; $i < self::SIZE; $i++) {
            if (! $this->reserved[6][$i]) {
                $this->setFunctionModule($i, 6, $i % 2 === 0);
            }

            if (! $this->reserved[$i][6]) {
                $this->setFunctionModule(6, $i, $i % 2 === 0);
            }
        }

        $this->setFunctionModule(8, self::VERSION * 4 + 9, true);

        for ($i = 0; $i <= 8; $i++) {
            if ($i !== 6) {
                $this->setFunctionModule(8, $i, false);
                $this->setFunctionModule($i, 8, false);
            }
        }

        for ($i = self::SIZE - 8; $i < self::SIZE; $i++) {
            $this->setFunctionModule(8, $i, false);
            $this->setFunctionModule($i, 8, false);
        }
    }

    private function drawFinderPattern(int $centerX, int $centerY): void
    {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $x = $centerX + $dx;
                $y = $centerY + $dy;

                if ($x < 0 || $x >= self::SIZE || $y < 0 || $y >= self::SIZE) {
                    continue;
                }

                $distance = max(abs($dx), abs($dy));
                $this->setFunctionModule($x, $y, $distance !== 2 && $distance !== 4);
            }
        }
    }

    private function drawAlignmentPattern(int $centerX, int $centerY): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $this->setFunctionModule($centerX + $dx, $centerY + $dy, max(abs($dx), abs($dy)) !== 1);
            }
        }
    }

    /**
     * @param  array<int, int>  $codewords
     */
    private function drawCodewords(array $codewords): void
    {
        $bits = [];
        foreach ($codewords as $codeword) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($codeword >> $i) & 1;
            }
        }

        $bitIndex = 0;
        $direction = -1;

        for ($right = self::SIZE - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }

            for ($vertical = 0; $vertical < self::SIZE; $vertical++) {
                $y = $direction === 1 ? $vertical : self::SIZE - 1 - $vertical;

                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;

                    if ($this->reserved[$y][$x]) {
                        continue;
                    }

                    $dark = ($bits[$bitIndex] ?? 0) === 1;
                    $bitIndex++;

                    if (($x + $y) % 2 === 0) {
                        $dark = ! $dark;
                    }

                    $this->modules[$y][$x] = $dark;
                }
            }

            $direction *= -1;
        }
    }

    private function drawFormatBits(int $mask): void
    {
        $bits = self::formatBits($mask);

        for ($i = 0; $i <= 5; $i++) {
            $this->setFunctionModule(8, $i, self::bit($bits, $i));
        }

        $this->setFunctionModule(8, 7, self::bit($bits, 6));
        $this->setFunctionModule(8, 8, self::bit($bits, 7));
        $this->setFunctionModule(7, 8, self::bit($bits, 8));

        for ($i = 9; $i < 15; $i++) {
            $this->setFunctionModule(14 - $i, 8, self::bit($bits, $i));
        }

        for ($i = 0; $i < 8; $i++) {
            $this->setFunctionModule(self::SIZE - 1 - $i, 8, self::bit($bits, $i));
        }

        for ($i = 8; $i < 15; $i++) {
            $this->setFunctionModule(8, self::SIZE - 15 + $i, self::bit($bits, $i));
        }

        $this->setFunctionModule(8, self::VERSION * 4 + 9, true);
    }

    private static function formatBits(int $mask): int
    {
        $data = (0b01 << 3) | $mask;
        $remainder = $data << 10;

        for ($i = 14; $i >= 10; $i--) {
            if ((($remainder >> $i) & 1) !== 0) {
                $remainder ^= 0x537 << ($i - 10);
            }
        }

        return (($data << 10) | $remainder) ^ 0x5412;
    }

    private static function bit(int $value, int $index): bool
    {
        return (($value >> $index) & 1) !== 0;
    }

    private function setFunctionModule(int $x, int $y, bool $dark): void
    {
        if ($x < 0 || $x >= self::SIZE || $y < 0 || $y >= self::SIZE) {
            return;
        }

        $this->modules[$y][$x] = $dark;
        $this->reserved[$y][$x] = true;
    }

    private function toSvg(int $moduleSize, int $quietZone): string
    {
        $viewBoxSize = self::SIZE + ($quietZone * 2);
        $pixelSize = $viewBoxSize * $moduleSize;
        $path = '';

        for ($y = 0; $y < self::SIZE; $y++) {
            for ($x = 0; $x < self::SIZE; $x++) {
                if ($this->modules[$y][$x]) {
                    $path .= 'M'.($x + $quietZone).' '.($y + $quietZone).'h1v1h-1z';
                }
            }
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<svg xmlns="http://www.w3.org/2000/svg" width="'.$pixelSize.'" height="'.$pixelSize.'" viewBox="0 0 '.$viewBoxSize.' '.$viewBoxSize.'" shape-rendering="crispEdges">'
            .'<rect width="100%" height="100%" fill="#fff"/>'
            .'<path d="'.$path.'" fill="#111827"/>'
            .'</svg>';
    }
}
