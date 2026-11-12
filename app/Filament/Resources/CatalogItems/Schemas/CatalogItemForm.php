<?php

namespace App\Filament\Resources\CatalogItems\Schemas;

use App\Enums\CatalogItemStatus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CatalogItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basisinformatie')
                    ->schema([
                        TextInput::make('name')
                            ->label('Naam')
                            ->placeholder('bijv. Apollo (Brits Korthaar), Suzuki Swift, Website Audit')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Unieke identificatie voor de openbare pagina, bijv. kater-apollo'),

                        Select::make('category_id')
                            ->label('Categorie')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('type', 'catalog')
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecteer een categorie (optioneel)'),

                        Select::make('status')
                            ->label('Beschikbaarheidsstatus')
                            ->options([
                                CatalogItemStatus::AVAILABLE->value    => 'Beschikbaar',
                                CatalogItemStatus::RESERVED->value     => 'Gereserveerd',
                                CatalogItemStatus::SOLD_ADOPTED->value => 'Verkocht / Geadopteerd',
                                CatalogItemStatus::HIDDEN->value       => 'Verborgen (Concept)',
                            ])
                            ->default(CatalogItemStatus::AVAILABLE->value)
                            ->required(),

                        TextInput::make('price_minor')
                            ->label('Prijs (€)')
                            ->numeric()
                            ->prefix('€')
                            ->placeholder('bijv. 1250.00')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null)
                            ->helperText('Voer het bedrag in euro in. Wordt opgeslagen in centen (BCMath). Leeg laten voor "Prijs op aanvraag".'),

                        TextInput::make('whatsapp_prefill_text')
                            ->label('Standaard WhatsApp-bericht')
                            ->placeholder('bijv. Hallo, ik heb interesse in kater Apollo')
                            ->maxLength(255)
                            ->helperText('Tekst die automatisch wordt ingevuld wanneer een bezoeker op de WhatsApp-knop klikt.'),

                        Textarea::make('description')
                            ->label('Beschrijving')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Gedetailleerde beschrijving, specificaties, kenmerken...'),
                    ])->columns(2),

                Section::make('Branchespecifieke kenmerken (JSON-attributen)')
                    ->schema([
                        KeyValue::make('attributes')
                            ->label('Kenmerken & Specificaties')
                            ->keyLabel('Kenmerk (bijv. ras, geslacht, kleur, geboortedatum, bouwjaar)')
                            ->valueLabel('Waarde (bijv. Brits Korthaar, Kater, Blauw, 2024-05-10)')
                            ->reorderable()
                            ->columnSpanFull()
                            ->helperText('Flexibele branchespecifieke attributen opgeslagen in een gestructureerd JSON-veld.'),
                    ]),
            ]);
    }
}
