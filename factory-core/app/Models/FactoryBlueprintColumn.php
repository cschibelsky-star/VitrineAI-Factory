<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryBlueprintColumn extends Model
{
    use HasFactory;

    protected $table = 'factory_blueprint_fields';

    protected $fillable = [
        'blueprint_entity_id',
        'name',
        'slug',
        'type',
        'nullable',
        'required',
        'searchable',
        'sortable',
        'order_column',
        'rules',
        'metadata',
    ];

    protected $casts = [
        'nullable' => 'boolean',
        'required' => 'boolean',
        'searchable' => 'boolean',
        'sortable' => 'boolean',
        'rules' => 'array',
        'metadata' => 'array',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(FactoryBlueprintEntity::class, 'blueprint_entity_id');
    }
}
