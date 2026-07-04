<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use App\Models\FactoryTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ProvisionadorFactory extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Provisionador';
    protected static ?string $title = 'Provisionador Automático';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static string $view = 'filament.pages.provisionador-factory';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'branch' => 'main',
            'environment' => 'production',
            'status' => 'draft',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('template_id')
                    ->label('Template')
                    ->options(FactoryTemplate::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nome do Projeto')
                    ->required(),

                Forms\Components\TextInput::make('client_name')
                    ->label('Cliente'),

                Forms\Components\TextInput::make('domain')
                    ->label('Domínio'),

                Forms\Components\TextInput::make('github_repository')
                    ->label('Repositório GitHub'),

                Forms\Components\TextInput::make('branch')
                    ->label('Branch')
                    ->default('main'),

                Forms\Components\TextInput::make('deploy_path')
                    ->label('Caminho no Servidor'),

                Forms\Components\Select::make('environment')
                    ->label('Ambiente')
                    ->options([
                        'production' => 'Produção',
                        'homologation' => 'Homologação',
                        'development' => 'Desenvolvimento',
                    ])
                    ->default('production'),

                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $template = FactoryTemplate::find($data['template_id']);

        FactoryProject::create([
            'name' => $data['name'],
            'client_name' => $data['client_name'] ?? null,
            'product' => $template?->name,
            'domain' => $data['domain'] ?? null,
            'github_repository' => $data['github_repository'] ?? $template?->base_repository,
            'branch' => $data['branch'] ?? $template?->default_branch ?? 'main',
            'deploy_path' => $data['deploy_path'] ?? null,
            'environment' => $data['environment'] ?? 'production',
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
        ]);

        Notification::make()
            ->title('Projeto criado no provisionador')
            ->success()
            ->send();

        $this->form->fill();
    }
}
