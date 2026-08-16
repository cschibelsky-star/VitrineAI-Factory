<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryMissionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'step_id',
        'agent_id',
        'level',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(FactoryMission::class, 'mission_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FactoryMissionStep::class, 'step_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(FactoryAgent::class, 'agent_id');
    }
}
