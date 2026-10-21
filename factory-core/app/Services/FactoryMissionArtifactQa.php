<?php

namespace App\Services;

use App\Models\FactoryMissionStep;
use Illuminate\Support\Facades\Storage;

/** Independent, read-only artifact verification: no generation or mutation. */
class FactoryMissionArtifactQa
{
    public function verify(FactoryMissionStep $step, array $evidence): array
    {
        if (($evidence['action'] ?? '') !== FactoryMissionStepExecutor::INTERNAL_SCAFFOLD
            || ($evidence['scope'] ?? '') !== 'factory-internal-test-only') {
            return ['approved' => false, 'reason' => 'unsupported_evidence'];
        }

        $expected = sprintf('factory-builds/factory-internal-mission-%d-step-%d', $step->mission_id, $step->id);
        if (($evidence['artifact_path'] ?? null) !== $expected) {
            return ['approved' => false, 'reason' => 'wrong_artifact_path'];
        }

        $files = $evidence['files'] ?? [];
        $hashes = $evidence['sha256'] ?? [];
        if (! is_array($files) || count($files) !== 4 || count(array_unique($files)) !== 4 || ! is_array($hashes)) {
            return ['approved' => false, 'reason' => 'invalid_manifest'];
        }

        $required = ['README.md', 'composer.json', '.env.example', 'routes/web.php'];
        $expectedFiles = array_map(fn (string $suffix): string => $expected . '/' . $suffix, $required);
        sort($expectedFiles);
        sort($files);
        if ($files !== $expectedFiles || count($hashes) !== 4) {
            return ['approved' => false, 'reason' => 'unexpected_files'];
        }

        // Reject unknown or extra keys even when expected file hashes match.
        if (array_diff(array_keys($hashes), $expectedFiles) !== [] || array_diff($expectedFiles, array_keys($hashes)) !== []) {
            return ['approved' => false, 'reason' => 'invalid_hash_manifest'];
        }

        $disk = Storage::disk('local');
        foreach ($expectedFiles as $file) {
            if (! $disk->exists($file) || ! is_string($hashes[$file] ?? null)
                || ! hash_equals($hashes[$file], hash('sha256', $disk->get($file)))) {
                return ['approved' => false, 'reason' => 'missing_or_modified_artifact'];
            }
        }
        return ['approved' => true, 'reason' => 'verified_artifact_hashes', 'files_verified' => 4];
    }
}
