<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Customer;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Demo
{
    public function create(): User
    {
        return DB::transaction(function () {
            $u = User::create(['name' => 'Visitante demo', 'email' => 'demo-'.Str::uuid().'@example.invalid', 'password' => Hash::make(Str::random(64))]);
            $b = Business::create(['name' => 'Taller del Sur · ejemplo', 'industry' => 'Carpintería', 'is_demo' => true, 'expires_at' => now()->addDay()]);
            BusinessMembership::create(['user_id' => $u->id, 'business_id' => $b->id]);
            $this->seed($b);

            return $u;
        });
    }

    public function seed(Business $b): void
    {
        $flow = app(Workflow::class);
        $c = Customer::create(['business_id' => $b->id, 'name' => 'Marina López · ejemplo', 'address' => 'Dirección ficticia, Buenos Aires', 'notes' => 'Datos ficticios para explorar el recorrido.']);
        $c2 = Customer::create(['business_id' => $b->id, 'name' => 'Local La Esquina · ejemplo']);
        $d = ['title' => 'Mueble bajo mesada', 'scope' => 'Fabricación de mueble de melamina blanca e instalación. Ejemplo ficticio.', 'exclusions' => 'No incluye mesada ni conexiones sanitarias.', 'issued_on' => now($b->timezone)->toDateString(), 'expires_on' => now($b->timezone)->addDays(15)->toDateString(), 'currency' => 'ARS', 'items' => [['description' => 'Materiales y fabricación', 'quantity' => '1', 'unit_price' => '240000', 'category' => 'material'], ['description' => 'Instalación', 'quantity' => '1', 'unit_price' => '60000', 'category' => 'mano de obra']], 'discount' => '0', 'tax_rate' => '0', 'advance' => '90000', 'conditions' => 'Ejemplo: anticipo del 30%, saldo al finalizar.'];
        $q = $flow->createQuote($b->id, $c->id, $d);
        [$v] = $flow->publish($q);
        $flow->decide($v, ['name' => 'Marina (ejemplo)', 'decision' => 'aceptado', 'confirmed' => true]);
        $j = $flow->convert($q);
        $j->update(['status' => 'en curso', 'next_action' => 'Confirmar medidas antes del corte', 'action_on' => now($b->timezone)->subDay()->toDateString(), 'due_on' => now($b->timezone)->addDays(3)->toDateString()]);
        $flow->pay($j, ['amount' => '90000', 'currency' => 'ARS', 'paid_on' => now($b->timezone)->toDateString(), 'method' => 'transferencia', 'reference' => 'Anticipo ficticio', 'idempotency_key' => (string) Str::uuid()]);
        $d['title'] = 'Estantes para local';
        $d['advance'] = '0';
        $d['scope'] = 'Tres estantes de madera. Datos de ejemplo.';
        $d['items'] = [['description' => 'Estantes a medida', 'quantity' => '3', 'unit_price' => '45000', 'category' => 'material']];
        $q2 = $flow->createQuote($b->id, $c2->id, $d);
        $flow->publish($q2);
        Job::create(['business_id' => $b->id, 'customer_id' => $c2->id, 'title' => 'Ajuste de puerta · ejemplo', 'scope' => 'Ajustar bisagras y cierre.', 'amount_minor' => 3500000, 'currency' => 'ARS', 'status' => 'terminado']);
        Job::create(['business_id' => $b->id, 'customer_id' => $c->id, 'title' => 'Reparación especial · ejemplo USD', 'scope' => 'Trabajo de muestra en otra moneda.', 'amount_minor' => 15000, 'currency' => 'USD', 'status' => 'en espera', 'next_action' => 'Confirmar disponibilidad']);
    }
}
