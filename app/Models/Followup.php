<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Followup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'data',
        'canal',
        'observacao',
        'status'
    ];

    protected $casts = [
        'data' => 'date'
    ];

    public function lead()
    {
        return $this->belongsTo(\App\Models\Lead::class);
    }
}
