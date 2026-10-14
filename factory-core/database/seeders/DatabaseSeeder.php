<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FactoryCoreSeeder::class,
            EngineCoreSeeder::class,
            BlueprintEngineSeeder::class,
            CapabilityEngineSeeder::class,
            MissionAgentSeeder::class,
        ]);

        $email = env('FACTORY_ADMIN_EMAIL');
        $password = env('FACTORY_ADMIN_PASSWORD');

        if ($email && $password) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => env('FACTORY_ADMIN_NAME', 'Factory Admin'),
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
