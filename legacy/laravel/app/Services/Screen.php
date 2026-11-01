<?php

namespace App\Services;

final class Screen
{
    public const LABELS = ['landing' => 'Página de inicio', 'dashboard' => 'Panel principal', 'customers' => 'Clientes', 'quote-editor' => 'Editor de presupuestos', 'quotes' => 'Presupuestos', 'public-quote' => 'Presupuesto del cliente', 'jobs' => 'Trabajos y cobros', 'settings' => 'Configuración', 'register' => 'Crear cuenta de prueba', 'login' => 'Ingreso', 'feedback' => 'Feedback', 'other' => 'Otra pantalla'];

    public static function fromPath(string $path): string
    {
        $path = trim($path, '/');

        return match (true) {
            $path === '' => 'landing', $path === 'app' => 'dashboard',str_starts_with($path, 'p/') => 'public-quote',str_starts_with($path, 'app/customers') => 'customers',
            $path === 'app/quotes/new' || preg_match('#^app/quotes/\d+/edit$#', $path) === 1 => 'quote-editor',str_starts_with($path, 'app/quotes') || str_starts_with($path, 'app/versions') => 'quotes',str_starts_with($path, 'app/jobs') => 'jobs',str_starts_with($path, 'app/settings') => 'settings',array_key_exists($path, self::LABELS) => $path,default => 'other'
        };
    }
}
