<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactoryProject extends Model
{
    protected $fillable = [
        'name',
        'client_name',
        'product',
        'domain',
        'github_repository',
        'branch',
        'deploy_path',
        'environment',
        'status',
        'notes',
    ];

    public function provisioningLogs(): HasMany
    {
        return $this->hasMany(FactoryProvisioningLog::class);
    }
}
