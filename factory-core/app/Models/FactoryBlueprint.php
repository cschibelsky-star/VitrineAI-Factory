<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryBlueprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'status',
        'version',
        'source_product_id',
        'description',
        'blueprint_dna',
    ];

    protected $casts = [
        'blueprint_dna' => 'array',
    ];

    public function sourceProduct(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'source_product_id');
    }

    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(FactoryCapability::class, 'factory_blueprint_capability', 'blueprint_id', 'capability_id')
            ->withTimestamps();
    }

    public function missions(): HasMany
    {
        return $this->hasMany(FactoryMission::class, 'blueprint_id');
    }
}
