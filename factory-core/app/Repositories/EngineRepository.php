<?php

namespace App\Repositories;

use App\Models\Engine;
use App\Repositories\Contracts\EngineRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EngineRepository implements EngineRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Engine::query()->with('engineType')->latest()->paginate($perPage);
    }

    public function findOrFail(int $id): Engine
    {
        return Engine::query()->with('engineType')->findOrFail($id);
    }

    public function create(array $data): Engine
    {
        return Engine::query()->create($data);
    }

    public function update(Engine $engine, array $data): Engine
    {
        $engine->update($data);

        return $engine->refresh();
    }

    public function delete(Engine $engine): void
    {
        $engine->delete();
    }
}
