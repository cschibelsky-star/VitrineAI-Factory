<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Engine extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_DEPRECATED = 'deprecated';

    protected $fillable = [
        'engine_type_id',
        'name',
        'slug',
        'code',
        'status',
        'version',
        'description',
        'config',
        'metadata',
        'is_core',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'metadata' => 'array',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function engineType(): BelongsTo
    {
        return $this->belongsTo(EngineType::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PLANNED => 'Planejado',
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_PAUSED => 'Pausado',
            self::STATUS_DEPRECATED => 'Depreciado',
        ];
    }
}
