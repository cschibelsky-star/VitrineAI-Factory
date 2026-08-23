<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryOpportunityResource\Pages;
use App\Models\FactoryOpportunity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryOpportunityResource extends Resource
{
    protected static ?string $model = FactoryOpportunity::class;
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Radar de Oportunidades';
    protected static ?string $modelLabel = 'Oportunidade';
    protected static ?string $pluralModelLabel = 'Oportunidades';
    protected static ?int $navigationSort = 55;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Oportunidade')
                ->schema([
                    Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(255),
                    Forms\Components\Select::make('opportunity_type')->label('Tipo')->options([
                        'public_procurement' => 'Compra / Licitação Pública',
                        'public_funding' => 'Programa / Recurso Público',
                        'parliamentary_amendment' => 'Emenda Parlamentar',
                        'price_registry' => 'Ata de Registro de Preços',
                        'grant' => 'Edital / Fomento',
                        'cultural_call' => 'Edital Cultural',
                        'private_funding' => 'Fundo / Instituto Privado',
                        'partnership' => 'Parceria',
                        'other' => 'Outra',
                    ])->required(),
                    Forms\Components\Select::make('status')->label('Status')->options([
                        'identified' => 'Identificada',
                        'qualified' => 'Qualificada',
                        'preparing' => 'Em preparação',
                        'applied' => 'Inscrita / Protocolada',
                        'won' => 'Aprovada / Vencida',
                        'lost' => 'Não aprovada',
                        'expired' => 'Expirada',
                        'discarded' => 'Descartada',
                    ])->default('identified')->required(),
                    Forms\Components\TextInput::make('organization')->label('Órgão / Instituição'),
                    Forms\Components\TextInput::make('territory')->label('Território'),
                    Forms\Components\TextInput::make('source')->label('Fonte'),
                    Forms\Components\TextInput::make('source_url')->label('Link da fonte')->url(),
                    Forms\Components\DateTimePicker::make('deadline_at')->label('Prazo'),
                    Forms\Components\TextInput::make('match_score')->label('Aderência (%)')->numeric()->minValue(0)->maxValue(100),
                ])->columns(2),

            Forms\Components\Section::make('Inteligência e execução')
                ->schema([
                    Forms\Components\Textarea::make('match_analysis')->label('Análise de aderência')->rows(8)->columnSpanFull(),
                    Forms\Components\Textarea::make('requirements')->label('Requisitos')->rows(8)->columnSpanFull(),
                    Forms\Components\Textarea::make('gaps')->label('Lacunas')->rows(8)->columnSpanFull(),
                    Forms\Components\Textarea::make('action_plan')->label('Plano de ação')->rows(10)->columnSpanFull(),
                    Forms\Components\Textarea::make('evidence')->label('Evidências')->rows(8)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('match_score', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Oportunidade')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('opportunity_type')->label('Tipo')->badge(),
                Tables\Columns\TextColumn::make('organization')->label('Órgão / Instituição')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('match_score')->label('Aderência')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('deadline_at')->label('Prazo')->dateTime('d/m/Y H:i')->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('profile_type')->label('Perfil')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'identified' => 'Identificada',
                    'qualified' => 'Qualificada',
                    'preparing' => 'Em preparação',
                    'applied' => 'Inscrita / Protocolada',
                    'won' => 'Aprovada / Vencida',
                    'lost' => 'Não aprovada',
                    'expired' => 'Expirada',
                    'discarded' => 'Descartada',
                ]),
                Tables\Filters\SelectFilter::make('profile_type')->options([
                    'culture' => 'Perfil Cultural',
                    'public_supplier' => 'Fornecedor Público',
                    'nonprofit_funding' => 'Terceiro Setor',
                    'government_funding' => 'Captação Governamental',
                    'business_digital' => 'Negócio Digital',
                    'generic' => 'Geral',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryOpportunities::route('/'),
            'create' => Pages\CreateFactoryOpportunity::route('/create'),
            'edit' => Pages\EditFactoryOpportunity::route('/{record}/edit'),
        ];
    }
}
