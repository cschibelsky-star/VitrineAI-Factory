<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryCapabilityVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'capability_id',
        'version',
        'status',
        'schema',
        'published_at',
    ];

    protected $casts = [
        'schema' => 'array',
        'published_at' => 'datetime',
    ];

    public function capability(): BelongsTo
    {
        return $this->belongsTo(FactoryCapability::class, 'capability_id');
    }
}
