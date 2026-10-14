<?php

namespace App\Support;

/** Indian-system amount in words for receipts: 125000.50 → "Rupees One Lakh Twenty-Five Thousand and Fifty Paise Only". */
final class AmountInWords
{
    private const ONES = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
        'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public static function rupees(float|string $amount): string
    {
        $amount = round((float) $amount, 2);
        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        $words = 'Rupees '.($rupees === 0 ? 'Zero' : self::number($rupees));
        if ($paise > 0) {
            $words .= ' and '.self::number($paise).' Paise';
        }

        return $words.' Only';
    }

    private static function number(int $n): string
    {
        $parts = [];
        foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand'], [100, 'Hundred']] as [$unit, $name]) {
            if ($n >= $unit) {
                $parts[] = self::number(intdiv($n, $unit)).' '.$name;
                $n %= $unit;
            }
        }
        if ($n > 0) {
            $parts[] = $n < 20 ? self::ONES[$n] : self::TENS[intdiv($n, 10)].($n % 10 ? '-'.self::ONES[$n % 10] : '');
        }

        return implode(' ', $parts);
    }
}
