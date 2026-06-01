<?php

namespace App\Support;

class Money
{
    public static function mad(int|float|null $minorUnits, int $decimals = 2): string
    {
        $amount = ((float) ($minorUnits ?? 0)) / 100;

        return number_format($amount, $decimals).' MAD';
    }
}
