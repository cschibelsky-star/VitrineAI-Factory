<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactoryTemplate extends Model
{
    protected $fillable = [
        'name',
        'product_type',
        'base_repository',
        'default_branch',
        'install_commands',
        'status',
        'notes',
    ];
}
