<?php

namespace App\Services;

use App\Models\FactoryMissionStep;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Explicit, non-deploying internal Factory capability.
 * No shell commands, external product files, arbitrary paths or network calls.
 */
class FactoryMissionStepExecutor
{
    public const INTERNAL_SCAFFOLD = 'factory.internal_scaffold';

    public function execute(FactoryMissionStep $step): array
    {
        if (($step->payload['action'] ?? null) !== self::INTERNAL_SCAFFOLD) {
            throw new RuntimeException('Acao de missao nao autorizada.');
        }

        if (app()->environment('production')) {
            throw new RuntimeException('Executor de prova interna desabilitado em producao.');
        }

        $name = sprintf('Factory Internal Mission %d Step %d', $step->mission_id, $step->id);
        $path = 'factory-builds/' . Str::slug($name);
        $disk = Storage::disk('local');

        // Do not overwrite artifacts from an earlier attempt or another process.
        if ($disk->exists($path . '/README.md')) {
            throw new RuntimeException('Artefato existente: abrir nova missao ou reconciliar manualmente.');
        }

        $result = app(FactoryMiniBuilder::class)->generate($name);
        if (($result['path'] ?? null) !== $path) {
            throw new RuntimeException('O Builder retornou um destino inesperado.');
        }

        $hashes = [];
        foreach ($result['files'] ?? [] as $file) {
            if (! str_starts_with($file, $path . '/') || ! $disk->exists($file)) {
                throw new RuntimeException('Artefato ausente ou fora da area autorizada.');
            }
            $hashes[$file] = hash('sha256', $disk->get($file));
        }

        return [
            'action' => self::INTERNAL_SCAFFOLD,
            'scope' => 'factory-internal-test-only',
            'artifact_path' => $path,
            'files' => $result['files'],
            'sha256' => $hashes,
        ];
    }
}
