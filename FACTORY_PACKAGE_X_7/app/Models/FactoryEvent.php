<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactoryEvent extends Model
{
    protected $fillable = [
        'event',
        'source',
        'target',
        'status',
        'payload',
        'message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
