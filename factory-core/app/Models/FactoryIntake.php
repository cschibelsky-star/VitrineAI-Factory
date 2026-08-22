<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryIntake extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'origin',
        'status',
        'priority',
        'request',
        'references',
        'profile_dna',
        'master_prompt',
        'ai_analysis',
        'analysis_status',
        'analyzed_at',
        'intake_dna',
        'product_id',
    ];

    protected $casts = [
        'references' => 'array',
        'profile_dna' => 'array',
        'ai_analysis' => 'array',
        'intake_dna' => 'array',
        'analyzed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }
}
