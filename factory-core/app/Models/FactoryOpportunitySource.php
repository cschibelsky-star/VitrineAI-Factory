<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryOpportunitySource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'scope',
        'status',
        'connector_type',
        'base_url',
        'supported_profile_types',
        'supported_opportunity_types',
        'mapping_contract',
        'source_dna',
        'last_sync_at',
        'last_sync_status',
        'last_sync_evidence',
    ];

    protected $casts = [
        'supported_profile_types' => 'array',
        'supported_opportunity_types' => 'array',
        'mapping_contract' => 'array',
        'source_dna' => 'array',
        'last_sync_at' => 'datetime',
        'last_sync_evidence' => 'array',
    ];

    public function opportunities(): HasMany
    {
        return $this->hasMany(FactoryOpportunity::class, 'source_id');
    }
}
