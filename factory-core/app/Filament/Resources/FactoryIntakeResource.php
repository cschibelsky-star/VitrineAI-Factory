<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryIntakeResource\Pages;
use App\Models\FactoryIntake;
use App\Models\FactoryProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryIntakeResource extends Resource
{
    protected static ?string $model = FactoryIntake::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Criar com IA';
    protected static ?string $modelLabel = 'Entrada da Factory';
    protected static ?string $pluralModelLabel = 'Entradas da Factory';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('O que você quer criar?')
                ->description('Descreva a necessidade em linguagem simples. A Factory usará este briefing para estruturar o perfil/DNA e o Prompt Mestre do projeto.')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Nome curto da ideia ou projeto')
                        ->placeholder('Ex.: CRM para barbearia')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('request')
                        ->label('Descreva o que você precisa')
                        ->placeholder('Ex.: Preciso de um CRM para minha barbearia com agenda, clientes, comissões e financeiro.')
                        ->rows(7)
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Origem e contexto')
                ->schema([
                    Forms\Components\Select::make('origin')
                        ->label('Como este projeto nasce?')
                        ->options([
                            'new_idea' => 'Criar algo novo',
                            'existing_evolution' => 'Evoluir projeto existente',
                            'catalog_product' => 'Implantar produto do catálogo',
                            'reference_project' => 'Usar projeto como referência',
                        ])
                        ->default('new_idea')
                        ->required(),
                    Forms\Components\Select::make('type')->label('Tipo da demanda')->options([
                        'new_project' => 'Novo projeto',
                        'evolution' => 'Evolução',
                        'correction' => 'Correção',
                        'integration' => 'Integração',
                    ])->default('new_project')->required(),
                    Forms\Components\Select::make('priority')->label('Prioridade')->options([
                        'low' => 'Baixa', 'normal' => 'Normal', 'high' => 'Alta', 'critical' => 'Crítica',
                    ])->default('normal')->required(),
                    Forms\Components\Select::make('product_id')
                        ->label('Projeto existente / produto vinculado')
                        ->options(fn () => FactoryProduct::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload(),
                ])->columns(2),

            Forms\Components\Section::make('Referências do cliente')
                ->description('Use logo, site, redes sociais, canal, arquivos, catálogo ou exemplos para entender o perfil do cliente. Referências orientam a solução; não devem ser copiadas automaticamente.')
                ->schema([
                    Forms\Components\TagsInput::make('references')
                        ->label('Links e referências')
                        ->placeholder('https://site-do-cliente.com.br')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Inteligência da Factory')
                ->description('Resultado da análise de IA. O perfil/DNA representa o contexto persistente; o Prompt Mestre inicia a construção ou o provisionamento.')
                ->schema([
                    Forms\Components\Select::make('analysis_status')
                        ->label('Status da análise')
                        ->options([
                            'pending' => 'Pendente',
                            'analyzing' => 'Analisando',
                            'ready' => 'Pronta para aprovação',
                            'approved' => 'Aprovada',
                            'failed' => 'Falhou',
                        ])
                        ->default('pending'),
                    Forms\Components\Textarea::make('profile_dna')
                        ->label('Perfil / DNA estruturado')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: ['raw' => $state]) : $state)
                        ->rows(12)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('master_prompt')
                        ->label('Prompt Mestre')
                        ->rows(16)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('ai_analysis')
                        ->label('Análise técnica da IA')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: ['raw' => $state]) : $state)
                        ->rows(10)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Controle')
                ->schema([
                    Forms\Components\Select::make('status')->label('Status')->options([
                        'new' => 'Nova',
                        'triage' => 'Em triagem',
                        'approved' => 'Aprovada',
                        'rejected' => 'Descartada',
                        'converted' => 'Convertida em projeto',
                    ])->default('new')->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Entrada')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('origin')->label('Origem')->badge(),
                Tables\Columns\TextColumn::make('analysis_status')->label('IA')->badge(),
                Tables\Columns\TextColumn::make('priority')->label('Prioridade')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('product.name')->label('Projeto')->placeholder('—'),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryIntakes::route('/'),
            'create' => Pages\CreateFactoryIntake::route('/create'),
            'edit' => Pages\EditFactoryIntake::route('/{record}/edit'),
        ];
    }
}
