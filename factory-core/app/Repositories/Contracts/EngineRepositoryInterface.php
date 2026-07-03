<?php

namespace App\Repositories\Contracts;

use App\Models\Engine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EngineRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Engine;

    public function create(array $data): Engine;

    public function update(Engine $engine, array $data): Engine;

    public function delete(Engine $engine): void;
}
