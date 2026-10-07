<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryCapabilityRelation extends Model
{
    use HasFactory;

    protected $table = 'factory_capability_links';

    protected $fillable = [
        'capability_id',
        'related_capability_id',
        'blueprint_id',
        'product_id',
        'link_type',
        'status',
        'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function capability(): BelongsTo
    {
        return $this->belongsTo(FactoryCapability::class, 'capability_id');
    }

    public function relatedCapability(): BelongsTo
    {
        return $this->belongsTo(FactoryCapability::class, 'related_capability_id');
    }
}
