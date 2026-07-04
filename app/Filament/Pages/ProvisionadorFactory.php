<?php

namespace App\Filament\Pages;

use App\Factory\Services\ProvisioningService;
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
    protected static ?string $title = 'Provisionador Inteligente';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static string $view = 'filament.pages.provisionador-factory';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'environment' => 'production',
            'branch' => 'main',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('1. Produto e Template')
                ->schema([
                    Forms\Components\Select::make('template_id')
                        ->label('Template')
                        ->options(FactoryTemplate::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('environment')
                        ->label('Ambiente')
                        ->options([
                            'production' => 'Produção',
                            'homologation' => 'Homologação',
                            'development' => 'Desenvolvimento',
                        ])
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('2. Cliente')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nome do Projeto')->required(),
                    Forms\Components\TextInput::make('client_name')->label('Cliente')->required(),
                    Forms\Components\TextInput::make('domain')->label('Domínio')->required(),
                    Forms\Components\TextInput::make('admin_name')->label('Administrador'),
                    Forms\Components\TextInput::make('admin_email')->label('E-mail Admin')->email(),
                ])->columns(2),

            Forms\Components\Section::make('3. Deploy')
                ->schema([
                    Forms\Components\TextInput::make('github_repository')->label('Repositório GitHub'),
                    Forms\Components\TextInput::make('branch')->label('Branch')->default('main'),
                    Forms\Components\TextInput::make('deploy_path')->label('Caminho no Servidor')->required(),
                    Forms\Components\Textarea::make('notes')->label('Observações')->columnSpanFull(),
                ])->columns(2),
        ])->statePath('data');
    }

    public function provisionar(): void
    {
        $data = $this->form->getState();
        $template = FactoryTemplate::find($data['template_id']);

        $project = FactoryProject::create([
            'name' => $data['name'],
            'client_name' => $data['client_name'],
            'product' => $template?->name,
            'domain' => $data['domain'],
            'admin_name' => $data['admin_name'] ?? null,
            'admin_email' => $data['admin_email'] ?? null,
            'github_repository' => $data['github_repository'] ?: $template?->base_repository,
            'branch' => $data['branch'] ?: $template?->default_branch ?: 'main',
            'deploy_path' => $data['deploy_path'],
            'document_root' => rtrim($data['deploy_path'], '/') . '/public',
            'environment' => $data['environment'],
            'status' => 'draft',
            'provisioning_status' => 'pending',
            'cpanel_status' => 'pending',
            'health_status' => 'unknown',
            'notes' => $data['notes'] ?? null,
        ]);

        app(ProvisioningService::class)->run($project);

        Notification::make()
            ->title('Projeto criado e enviado para provisionamento')
            ->success()
            ->send();

        $this->form->fill();
    }
}
