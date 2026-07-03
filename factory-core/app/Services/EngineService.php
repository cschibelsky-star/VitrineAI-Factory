<?php

namespace App\Services;

use App\Models\Engine;
use App\Repositories\Contracts\EngineRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EngineService
{
    public function __construct(private readonly EngineRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $payload): Engine
    {
        $payload['slug'] = Str::slug($payload['slug'] ?? $payload['name']);
        $payload['code'] = strtoupper($payload['code']);
        $payload['status'] = $payload['status'] ?? Engine::STATUS_PLANNED;
        $payload['version'] = $payload['version'] ?? '0.1.0';

        return $this->repository->create($payload);
    }

    public function update(Engine $engine, array $payload): Engine
    {
        if (isset($payload['name']) || isset($payload['slug'])) {
            $payload['slug'] = Str::slug($payload['slug'] ?? $payload['name']);
        }

        if (isset($payload['code'])) {
            $payload['code'] = strtoupper($payload['code']);
        }

        return $this->repository->update($engine, $payload);
    }

    public function activate(Engine $engine): Engine
    {
        return $this->repository->update($engine, [
            'status' => Engine::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    public function pause(Engine $engine): Engine
    {
        return $this->repository->update($engine, [
            'status' => Engine::STATUS_PAUSED,
            'is_active' => false,
        ]);
    }
}
