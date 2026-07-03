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
        'status',
        'description',
        'agent_dna',
    ];

    protected $casts = [
        'agent_dna' => 'array',
    ];

    public function missions(): HasMany
    {
        return $this->hasMany(FactoryMission::class, 'agent_id');
    }
}
