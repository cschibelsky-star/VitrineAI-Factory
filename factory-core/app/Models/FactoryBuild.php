<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryBuild extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'mission_id', 'environment', 'status', 'version', 'image',
        'commit_sha', 'log_location', 'evidence', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(FactoryMission::class, 'mission_id');
    }

    public function homologations(): HasMany
    {
        return $this->hasMany(FactoryHomologation::class, 'build_id');
    }

    public function releases(): HasMany
    {
        return $this->hasMany(FactoryRelease::class, 'build_id');
    }
}
