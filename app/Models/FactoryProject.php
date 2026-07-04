<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
