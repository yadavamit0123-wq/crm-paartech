<?php

namespace App\Support;

class PdfMoney
{
    public static function format(float|int|string|null $n, string $currencySymbol = '₹', bool $force2 = false): string
    {
        $n = (float) $n;
        if ($force2 || abs($n - round($n)) > 0.00001) {
            return $currencySymbol.number_format($n, 2);
        }

        return $currencySymbol.number_format($n, 0);
    }

    public static function symbol(?string $currency): string
    {
        return ($currency ?? 'INR') === 'USD' ? '$' : '₹';
    }
}
