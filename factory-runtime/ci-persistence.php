<?php

// CI-only probe: never print keys, ciphertext or application data.
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (app()->environment() !== 'local' || config('database.default') !== 'sqlite'
    || config('database.connections.sqlite.database') !== '/app/storage/app/factory.sqlite') {
    throw new RuntimeException('Persistence probe must use the isolated CI compose runtime.');
}

// The entrypoint exports the persisted key only to its own process tree.
// Load the same persisted key inside this independent docker exec process.
config(['app.key' => trim(file_get_contents('/app/storage/app/factory-app-key'))]);
$phase = $argv[1] ?? '';
$disk = Illuminate\Support\Facades\Storage::disk('local');
$marker = 'ci-persistence-probe';
if ($phase === 'prepare') {
    $type = App\Models\EngineType::create(['name' => 'CI Persistence', 'slug' => $marker]);
    App\Models\Engine::create([
        'name' => 'CI Persistence', 'slug' => $marker, 'code' => 'CI-PERSISTENCE',
        'engine_type_id' => $type->id, 'status' => App\Models\Engine::STATUS_PAUSED,
        'metadata' => ['probe' => $marker],
    ]);
    $disk->put($marker, Illuminate\Support\Facades\Crypt::encryptString($marker));
} elseif ($phase === 'verify') {
    $engine = App\Models\Engine::where('code', 'CI-PERSISTENCE')->firstOrFail();
    if ($engine->status !== App\Models\Engine::STATUS_PAUSED
        || $engine->engineType->slug !== $marker
        || ($engine->metadata['probe'] ?? null) !== $marker
        || Illuminate\Support\Facades\Crypt::decryptString($disk->get($marker)) !== $marker) {
        throw new RuntimeException('Persistent database, file or application key changed.');
    }
} else {
    throw new InvalidArgumentException('Expected prepare or verify.');
}
echo "Persistence probe {$phase}: PASS\n";
