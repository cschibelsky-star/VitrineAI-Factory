<?php

namespace Database\Seeders;

use App\Models\Followup;
use Illuminate\Database\Seeder;

class FollowupSeeder extends Seeder
{
    public function run(): void
    {
        Followup::query()->firstOrCreate(array (
  'data' => '2026-07-01',
  'canal' => 'Canal exemplo',
  'observacao' => 'Registro inicial gerado pela Factory.',
  'status' => 'Status exemplo',
));
    }
}
