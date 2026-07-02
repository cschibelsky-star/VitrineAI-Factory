<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'origem',
        'status',
        'valor_estimado'
    ];

    protected $casts = [
        'valor_estimado' => 'decimal:2'
    ];

}
