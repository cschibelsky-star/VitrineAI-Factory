<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposta extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'titulo',
        'valor',
        'status',
        'validade'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'validade' => 'date'
    ];

    public function lead()
    {
        return $this->belongsTo(\App\Models\Lead::class);
    }
}
