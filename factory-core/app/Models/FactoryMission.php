<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'result',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'mission_dna' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
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

    public function steps(): HasMany
    {
        return $this->hasMany(FactoryMissionStep::class, 'mission_id')->orderBy('order_column');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FactoryMissionLog::class, 'mission_id');
    }
}
