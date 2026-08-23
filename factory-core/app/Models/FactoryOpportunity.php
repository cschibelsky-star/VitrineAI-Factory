<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'intake_id',
        'product_id',
        'source_id',
        'profile_type',
        'opportunity_type',
        'status',
        'title',
        'organization',
        'territory',
        'source',
        'source_url',
        'external_id',
        'ingestion_status',
        'deadline_at',
        'match_score',
        'match_analysis',
        'requirements',
        'gaps',
        'action_plan',
        'evidence',
        'opportunity_dna',
        'raw_payload',
        'qualified_at',
        'applied_at',
        'closed_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'match_score' => 'decimal:2',
        'match_analysis' => 'array',
        'requirements' => 'array',
        'gaps' => 'array',
        'action_plan' => 'array',
        'evidence' => 'array',
        'opportunity_dna' => 'array',
        'raw_payload' => 'array',
        'qualified_at' => 'datetime',
        'applied_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function intake(): BelongsTo
    {
        return $this->belongsTo(FactoryIntake::class, 'intake_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }

    public function sourceRegistry(): BelongsTo
    {
        return $this->belongsTo(FactoryOpportunitySource::class, 'source_id');
    }
}
