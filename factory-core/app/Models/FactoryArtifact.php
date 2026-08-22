<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryArtifact extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'mission_id', 'stage', 'type', 'status', 'title', 'version',
        'location', 'evidence', 'metadata',
    ];

    protected $casts = [
        'evidence' => 'array',
        'metadata' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(FactoryMission::class, 'mission_id');
    }
}
