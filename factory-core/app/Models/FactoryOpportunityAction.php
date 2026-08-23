<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryOpportunityAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'type',
        'status',
        'priority',
        'title',
        'description',
        'owner_type',
        'owner',
        'due_at',
        'dependencies',
        'required_evidence',
        'completion_evidence',
        'action_dna',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'dependencies' => 'array',
        'required_evidence' => 'array',
        'completion_evidence' => 'array',
        'action_dna' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(FactoryOpportunity::class, 'opportunity_id');
    }
}
