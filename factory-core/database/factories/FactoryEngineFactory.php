<?php

namespace Database\Factories;

use App\Models\Engine;
use App\Models\EngineType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FactoryEngineFactory extends Factory
{
    protected $model = Engine::class;

    public function definition(): array
    {
        $name = 'Builder Engine';

        return [
            'engine_type_id' => EngineType::factory(),
            'name' => $name,
            'slug' => Str::slug($name . '-' . uniqid()),
            'code' => strtoupper('ENG-' . uniqid()),
            'status' => Engine::STATUS_PLANNED,
            'version' => '0.1.0',
            'description' => 'Engine da Vitrine IA Factory.',
            'config' => [],
            'metadata' => [],
            'is_core' => false,
            'is_active' => true,
        ];
    }
}
