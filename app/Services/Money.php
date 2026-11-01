<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

final class Money
{
    public static function decimal(string $value, int $places = 2, bool $positive = false): string
    {
        if (! preg_match('/^\d{1,9}(?:\.\d{1,'.$places.'})?$/D', $value) || ($positive && bccomp($value, '0', $places) <= 0)) {
            throw ValidationException::withMessages(['amount' => 'Usá un número positivo con punto decimal, sin separadores de miles.']);
        }

        return $value;
    }

    public static function minor(string $v): int
    {
        self::decimal($v);

        return (int) bcmul($v, '100', 0);
    }

    public static function round(string $v): int
    {
        if (bccomp($v, '99999999999', 5) > 0 || bccomp($v, '0', 5) < 0) {
            throw ValidationException::withMessages(['amount' => 'El importe calculado supera el máximo de 999.999.999,99 por documento.']);
        }

        return (int) bcadd($v, '0.5', 0);
    }

    public static function display(int|string $minor, string $currency = 'ARS'): string
    {
        $s = (string) $minor;
        $negative = str_starts_with($s, '-');
        $s = ltrim($s, '-');
        $s = str_pad($s, 3, '0', STR_PAD_LEFT);
        $whole = substr($s, 0, -2);
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole);

        return $currency.' '.($negative ? '-' : '').$whole.','.substr($s, -2);
    }

    public static function input(int|string $minor): string
    {
        return bcdiv((string) $minor, '100', 2);
    }

    public static function quote(array $data): array
    {
        $subtotal = 0;
        $items = [];
        foreach ($data['items'] as $index => $item) {
            try {
                $q = self::decimal((string) $item['quantity'], 3, true);
            } catch (ValidationException $e) {
                throw ValidationException::withMessages(['items.'.$index.'.quantity' => 'Ingresá una cantidad mayor a cero, con hasta 3 decimales y punto decimal.']);
            }
            try {
                $p = self::minor((string) $item['unit_price']);
            } catch (ValidationException $e) {
                throw ValidationException::withMessages(['items.'.$index.'.unit_price' => 'Ingresá un precio válido, no negativo, con hasta 2 decimales y punto decimal.']);
            }
            $line = self::round(bcmul($q, (string) $p, 5));
            $subtotal += $line;
            if ($subtotal > 99999999999) {
                throw ValidationException::withMessages(['amount' => 'El subtotal supera el máximo de 999.999.999,99.']);
            }
            $items[] = ['description' => $item['description'], 'quantity' => $q, 'unit_price' => (string) $item['unit_price'], 'category' => $item['category'] ?? null, 'total_minor' => $line];
        }
        $discount = self::minor((string) ($data['discount'] ?? '0'));
        if ($discount > $subtotal) {
            throw ValidationException::withMessages(['discount' => 'El descuento no puede superar el subtotal.']);
        }
        $rate = self::decimal((string) ($data['tax_rate'] ?? '0'));
        if (bccomp($rate, '100', 2) > 0) {
            throw ValidationException::withMessages(['tax_rate' => 'El impuesto debe estar entre 0 y 100%.']);
        }
        $tax = self::round(bcdiv(bcmul((string) ($subtotal - $discount), $rate, 4), '100', 5));
        $total = $subtotal - $discount + $tax;
        if ($total > 99999999999) {
            throw ValidationException::withMessages(['amount' => 'El total supera el máximo de 999.999.999,99.']);
        }
        $advance = self::minor((string) ($data['advance'] ?? '0'));
        if ($advance > $total) {
            throw ValidationException::withMessages(['advance' => 'El anticipo no puede superar el total.']);
        }

        return array_merge($data, ['items' => $items, 'subtotal_minor' => $subtotal, 'discount_minor' => $discount, 'tax_minor' => $tax, 'total_minor' => $total, 'advance_minor' => $advance]);
    }
}
