<?php

namespace App\Filament\Pages;

use App\Models\FactoryBlueprint;
use App\Services\FactoryMiniBuilder;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class FactoryBuilderPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationGroup = 'Vitrine IA Factory';

    protected static ?string $navigationLabel = 'Builder';

    protected static string $view = 'filament.pages.factory-builder-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('project_name')->label('Nome do Projeto')->required(),
            Forms\Components\Select::make('blueprint_id')->label('Blueprint')
                ->options(fn () => FactoryBlueprint::query()->pluck('name', 'id')->toArray())
                ->searchable(),
            Forms\Components\TagsInput::make('capabilities')->label('Capabilities'),
        ];
    }

    public function generate(): void
    {
        $payload = $this->form->getState();
        $blueprint = isset($payload['blueprint_id']) ? FactoryBlueprint::find($payload['blueprint_id']) : null;

        $result = app(FactoryMiniBuilder::class)->generate(
            $payload['project_name'],
            $blueprint,
            $payload['capabilities'] ?? []
        );

        Notification::make()
            ->title('Projeto gerado')
            ->body('Arquivos criados em: ' . $result['path'])
            ->success()
            ->send();
    }
}
