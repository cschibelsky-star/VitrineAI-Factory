<?php

namespace Database\Factories;

use App\Models\EngineType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FactoryEngineTypeFactory extends Factory
{
    protected $model = EngineType::class;

    public function definition(): array
    {
        $name = 'Core Engine Type';

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . uniqid()),
            'category' => 'core',
            'description' => 'Tipo de engine da Vitrine IA Factory.',
            'is_active' => true,
        ];
    }
}
