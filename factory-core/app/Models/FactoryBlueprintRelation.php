<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryBlueprintRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'blueprint_id',
        'source_entity_id',
        'target_entity_id',
        'name',
        'type',
        'foreign_key',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(FactoryBlueprint::class, 'blueprint_id');
    }

    public function sourceEntity(): BelongsTo
    {
        return $this->belongsTo(FactoryBlueprintEntity::class, 'source_entity_id');
    }

    public function targetEntity(): BelongsTo
    {
        return $this->belongsTo(FactoryBlueprintEntity::class, 'target_entity_id');
    }
}
