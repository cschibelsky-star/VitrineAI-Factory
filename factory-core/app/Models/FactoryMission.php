<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'priority',
        'product_id',
        'blueprint_id',
        'agent_id',
        'github_issue_url',
        'objective',
        'mission_dna',
    ];

    protected $casts = [
        'mission_dna' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(FactoryBlueprint::class, 'blueprint_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FactoryAgent::class, 'agent_id');
    }
}
