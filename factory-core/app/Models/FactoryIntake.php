<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryIntake extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'type', 'status', 'priority', 'request', 'intake_dna', 'product_id',
    ];

    protected $casts = [
        'intake_dna' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(FactoryProduct::class, 'product_id');
    }
}
