<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'specialty',
        'role',
        'status',
        'description',
        'skills',
        'agent_dna',
        'metadata',
    ];

    protected $casts = [
        'skills' => 'array',
        'agent_dna' => 'array',
        'metadata' => 'array',
    ];

    public function missions(): HasMany
    {
        return $this->hasMany(FactoryMission::class, 'agent_id');
    }

    public function missionSteps(): HasMany
    {
        return $this->hasMany(FactoryMissionStep::class, 'agent_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FactoryMissionLog::class, 'agent_id');
    }
}
