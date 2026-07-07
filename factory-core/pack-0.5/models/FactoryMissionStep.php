<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryMissionStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'agent_id',
        'name',
        'status',
        'order_column',
        'payload',
        'result',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(FactoryMission::class, 'mission_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FactoryAgent::class, 'agent_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FactoryMissionLog::class, 'step_id');
    }
}
