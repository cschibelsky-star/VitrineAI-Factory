<?php
// Disposable CI runtime only. No real credentials or production data.
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (getenv('CI') !== 'true' || app()->environment() !== 'local'
    || config('database.connections.sqlite.database') !== '/app/storage/app/factory.sqlite') {
    throw new RuntimeException('Isolated CI runtime required.');
}
$disk = Illuminate\Support\Facades\Storage::disk('local');
$runner = app(App\Services\FactoryMissionRunner::class);
$phase = $argv[1] ?? '';
if ($phase === 'prepare') {
    $mission = App\Models\FactoryMission::create(['title' => 'CI VIA real HTTP', 'status' => 'planned']);
    $first = $mission->steps()->create([
        'name' => 'Original', 'status' => 'pending', 'order_column' => 1,
        'payload' => ['action' => App\Services\FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
    ]);
    if ($runner->run($mission)->status !== 'completed') throw new RuntimeException('Initial run failed.');
    $second = $mission->steps()->create([
        'name' => 'Next agent', 'status' => 'pending', 'order_column' => 2,
        'payload' => ['action' => App\Services\FactoryMissionStepExecutor::INTERNAL_SCAFFOLD],
    ]);
    $oldFile = $first->fresh()->result['evidence']['files'][0];
    $disk->put($oldFile, 'Deliberate CI artifact failure');
    if ($runner->run($mission)->status !== 'blocked' || $second->fresh()->status !== 'pending') {
        throw new RuntimeException('Tampering did not block next stage.');
    }
    $ids = ['mission' => $mission->id, 'first' => $first->id, 'second' => $second->id, 'old_file' => $oldFile];
    $disk->put('ci-supervision-ids.json', json_encode($ids, JSON_THROW_ON_ERROR));
    echo json_encode($ids, JSON_THROW_ON_ERROR);
} else {
    $ids = json_decode($disk->get('ci-supervision-ids.json'), true, 512, JSON_THROW_ON_ERROR);
    $mission = App\Models\FactoryMission::findOrFail($ids['mission']);
    if ($phase === 'pending') {
        if ($mission->steps()->findOrFail($ids['first'])->status !== 'pending'
            || $mission->steps()->findOrFail($ids['second'])->status !== 'pending'
            || $mission->status !== 'blocked') throw new RuntimeException('Correction unexpectedly advanced execution.');
    } elseif ($phase === 'resume') {
        if ($runner->run($mission)->status !== 'completed'
            || $mission->steps()->where('status', 'completed')->count() !== 2
            || $mission->steps()->findOrFail($ids['first'])->result['attempt'] !== 2
            || ! $disk->exists($ids['old_file'])) throw new RuntimeException('Correction cycle failed.');
    } else throw new InvalidArgumentException('Unknown phase.');
    echo "Supervision probe {$phase}: PASS\n";
}
