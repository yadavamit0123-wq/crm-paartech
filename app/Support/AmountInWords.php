<?php

namespace App\Support;

class AmountInWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public static function inr(float $amount): string
    {
        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        if ($rupees === 0 && $paise === 0) {
            return 'Zero Rupees Only';
        }

        $words = self::convertIndian($rupees).' Rupees';
        if ($paise > 0) {
            $words .= ' and '.self::convertIndian($paise).' Paise';
        }

        return $words.' Only';
    }

    protected static function convertIndian(int $num): string
    {
        if ($num === 0) {
            return '';
        }

        $parts = [];

        $crore = intdiv($num, 10000000);
        $num %= 10000000;
        if ($crore) {
            $parts[] = self::convertHundreds($crore).' Crore';
        }

        $lakh = intdiv($num, 100000);
        $num %= 100000;
        if ($lakh) {
            $parts[] = self::convertHundreds($lakh).' Lakh';
        }

        $thousand = intdiv($num, 1000);
        $num %= 1000;
        if ($thousand) {
            $parts[] = self::convertHundreds($thousand).' Thousand';
        }

        if ($num) {
            $parts[] = self::convertHundreds($num);
        }

        return trim(implode(' ', $parts));
    }

    protected static function convertHundreds(int $num): string
    {
        $out = '';

        if ($num >= 100) {
            $out .= self::ONES[intdiv($num, 100)].' Hundred';
            $num %= 100;
            if ($num) {
                $out .= ' ';
            }
        }

        if ($num >= 20) {
            $out .= self::TENS[intdiv($num, 10)];
            $num %= 10;
            if ($num) {
                $out .= ' '.self::ONES[$num];
            }
        } elseif ($num > 0) {
            $out .= self::ONES[$num];
        }

        return trim($out);
    }
}
