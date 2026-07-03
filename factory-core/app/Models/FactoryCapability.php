<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FactoryCapability extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'type',
        'status',
        'version',
        'description',
        'capability_dna',
    ];

    protected $casts = [
        'capability_dna' => 'array',
    ];

    public function blueprints(): BelongsToMany
    {
        return $this->belongsToMany(FactoryBlueprint::class, 'factory_blueprint_capability', 'capability_id', 'blueprint_id')
            ->withTimestamps();
    }
}
