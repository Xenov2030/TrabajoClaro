<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_landing_and_auth_forms_are_available(): void
    {
        $this->get('/')->assertOk()->assertSee('Probar demo')->assertSee('DESARROLLADO POR NICOLAS AGUIRRES');
        $this->get('/register')->assertOk();
        $this->get('/login')->assertOk();
    }
}
