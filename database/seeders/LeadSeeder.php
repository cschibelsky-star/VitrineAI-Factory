<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        Lead::query()->firstOrCreate(array (
  'nome' => 'Nome exemplo',
  'email' => 'Email exemplo',
  'telefone' => 'Telefone exemplo',
  'origem' => 'Origem exemplo',
  'status' => 'Status exemplo',
  'valor_estimado' => 0,
));
    }
}
