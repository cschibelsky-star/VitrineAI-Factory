<?php

namespace App\Models;

use Database\Factories\FactoryEngineTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngineType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): FactoryEngineTypeFactory
    {
        return FactoryEngineTypeFactory::new();
    }

    public function engines(): HasMany
    {
        return $this->hasMany(Engine::class);
    }
}
