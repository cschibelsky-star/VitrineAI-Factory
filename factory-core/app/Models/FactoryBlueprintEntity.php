<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryBlueprintEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'blueprint_id',
        'name',
        'slug',
        'table_name',
        'model_name',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(FactoryBlueprint::class, 'blueprint_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FactoryBlueprintColumn::class, 'blueprint_entity_id')->orderBy('order_column');
    }
}
