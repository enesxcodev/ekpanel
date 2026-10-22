<?php

namespace Modules\Store\Services;

use Illuminate\Validation\ValidationException;

class StoreMoney
{
    /** @var array<string, int> */
    public const Currencies = ['TRY' => 2];

    public static function parse(string|int|null $amount, string $currency = 'TRY'): int
    {
        $digits = self::Currencies[$currency] ?? throw ValidationException::withMessages(['currency' => 'Desteklenmeyen para birimi.']);
        $amount = str_replace(',', '.', trim((string) $amount));
        $pattern = $digits === 0 ? '/^\d{1,9}$/D' : '/^\d{1,9}(?:\.\d{1,'.$digits.'})?$/D';
        if (! preg_match($pattern, $amount)) {
            throw ValidationException::withMessages(['price' => 'Geçerli bir para tutarı girin.']);
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $whole * (10 ** $digits) + (int) str_pad($fraction, $digits, '0');
    }

    public static function decimal(int $minor, string $currency = 'TRY'): string
    {
        $digits = self::Currencies[$currency] ?? 2;
        if ($digits === 0) {
            return (string) $minor;
        }
        $factor = 10 ** $digits;

        return intdiv($minor, $factor).'.'.str_pad((string) ($minor % $factor), $digits, '0', STR_PAD_LEFT);
    }

    public static function format(int $minor, string $currency = 'TRY'): string
    {
        return str_replace('.', ',', self::decimal($minor, $currency)).' '.($currency === 'TRY' ? 'TL' : $currency);
    }

    /** @return array{net: int, tax: int, gross: int} */
    public static function tax(int $amount, int $rate, bool $included): array
    {
        $denominator = $included ? 100 + $rate : 100;
        $tax = intdiv($amount * $rate + intdiv($denominator, 2), $denominator);

        return ['net' => $included ? $amount - $tax : $amount, 'tax' => $tax, 'gross' => $included ? $amount : $amount + $tax];
    }
}
