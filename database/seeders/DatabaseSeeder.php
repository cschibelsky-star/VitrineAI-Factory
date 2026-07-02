<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LeadSeeder::class);
        $this->call(PropostaSeeder::class);
        $this->call(FollowupSeeder::class);
    }
}