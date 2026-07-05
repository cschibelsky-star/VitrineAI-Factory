<?php

namespace App\Filament\Pages;

use App\Factory\Services\EnterpriseEventBus;
use App\Models\FactoryEvent;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EventsCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationLabel = 'Events Center';
    protected static ?string $title = 'Enterprise Events Center';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 20;
    protected static string $view = 'filament.pages.events-center';

    public function getEventsProperty()
    {
        return FactoryEvent::orderByDesc('created_at')->limit(50)->get();
    }

    public function gerarEventoTeste(): void
    {
        app(EnterpriseEventBus::class)->publish(
            'FactoryHealthChecked',
            'Factory',
            'FactoryBrain',
            ['status' => 'ok'],
            'Evento de teste registrado pelo barramento interno.'
        );

        Notification::make()->title('Evento registrado')->success()->send();
    }
}
