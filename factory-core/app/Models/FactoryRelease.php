<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'build_id', 'homologation_id', 'version', 'status', 'changelog',
        'release_dna', 'approved_at', 'deployed_at',
    ];

    protected $casts = [
        'release_dna' => 'array',
        'approved_at' => 'datetime',
        'deployed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }

    public function build(): BelongsTo
    {
        return $this->belongsTo(FactoryBuild::class, 'build_id');
    }

    public function homologation(): BelongsTo
    {
        return $this->belongsTo(FactoryHomologation::class, 'homologation_id');
    }
}
