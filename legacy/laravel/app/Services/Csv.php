<?php

namespace App\Services;

final class Csv
{
    public static function safe(mixed $value): string
    {
        $s = (string) ($value ?? '');
        // También proteger fórmulas después de espacios, controles o BOM.
        if (preg_match('/^[\s\x{FEFF}]*[=+@-]/u', $s) || preg_match('/^[\t\r\n]/', $s)) {
            return "'".$s;
        }

        return $s;
    }
}
