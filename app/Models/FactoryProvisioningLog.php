<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactoryProvisioningLog extends Model
{
    protected $fillable = [
        'factory_project_id',
        'step',
        'status',
        'message',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(FactoryProject::class, 'factory_project_id');
    }
}
