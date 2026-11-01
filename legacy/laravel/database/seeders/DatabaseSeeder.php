<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Services\Demo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Los datos de ejemplo solo se cargan en local/testing.');
        }
        if (User::where('email', 'ejemplo@example.test')->exists()) {
            return;
        }
        $u = User::create(['name' => 'Nicolás · ejemplo', 'email' => 'ejemplo@example.test', 'password' => Hash::make('SoloEjemploLocal2026!')]);
        $b = Business::create(['name' => 'Taller de ejemplo', 'industry' => 'Carpintería', 'is_demo' => true]);
        BusinessMembership::create(['business_id' => $b->id, 'user_id' => $u->id]);
        app(Demo::class)->seed($b);
    }
}
