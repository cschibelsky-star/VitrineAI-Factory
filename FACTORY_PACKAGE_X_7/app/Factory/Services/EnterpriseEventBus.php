<?php

namespace App\Factory\Services;

use App\Models\FactoryEvent;

class EnterpriseEventBus
{
    public function publish(string $event, string $source = 'Factory', ?string $target = null, array $payload = [], ?string $message = null): FactoryEvent
    {
        return FactoryEvent::create([
            'event' => $event,
            'source' => $source,
            'target' => $target,
            'status' => 'published',
            'payload' => $payload,
            'message' => $message,
            'processed_at' => now(),
        ]);
    }
}
