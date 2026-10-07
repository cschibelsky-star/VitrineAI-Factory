<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryBlueprintVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'blueprint_id',
        'version',
        'status',
        'schema',
        'notes',
        'published_at',
    ];

    protected $casts = [
        'schema' => 'array',
        'published_at' => 'datetime',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(FactoryBlueprint::class, 'blueprint_id');
    }
}
