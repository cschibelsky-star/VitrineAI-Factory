<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryRuntimeEngine extends Model
{
    use HasFactory;

    protected $table = 'factory_engines';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'status',
        'version',
        'description',
        'engine_dna',
    ];

    protected $casts = [
        'engine_dna' => 'array',
    ];
}
