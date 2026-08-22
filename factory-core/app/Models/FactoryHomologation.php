<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryHomologation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'build_id', 'status', 'url', 'health_status', 'checks',
        'evidence', 'acceptance_notes', 'accepted_at',
    ];

    protected $casts = [
        'checks' => 'array',
        'evidence' => 'array',
        'accepted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }

    public function build(): BelongsTo
    {
        return $this->belongsTo(FactoryBuild::class, 'build_id');
    }

    public function releases(): HasMany
    {
        return $this->hasMany(FactoryRelease::class, 'homologation_id');
    }
}
