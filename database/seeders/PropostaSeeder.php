<?php

namespace Database\Seeders;

use App\Models\Proposta;
use Illuminate\Database\Seeder;

class PropostaSeeder extends Seeder
{
    public function run(): void
    {
        Proposta::query()->firstOrCreate(array (
  'titulo' => 'Titulo exemplo',
  'valor' => 0,
  'status' => 'Status exemplo',
  'validade' => '2026-07-01',
));
    }
}
