<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'status',
        'version',
        'github_repository',
        'description',
        'product_dna',
    ];

    protected $casts = [
        'product_dna' => 'array',
    ];

    public function blueprints(): HasMany
    {
        return $this->hasMany(FactoryBlueprint::class, 'source_product_id');
    }

    public function missions(): HasMany
    {
        return $this->hasMany(FactoryMission::class, 'product_id');
    }
}
