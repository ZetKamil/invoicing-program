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
                Section::make('Podstawowe Informacje')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nazwa Pozycji')
                            ->placeholder('np. Apollo (Kocur Brytyjski), Suzuki Swift, Konsultacja SEO')
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
                            ->helperText('Identyfikator dla linku na stronie publicznej, np. kot-apollo'),

                        Select::make('category_id')
                            ->label('Kategoria')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('type', 'catalog')
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Wybierz kategorię (opcjonalnie)'),

                        Select::make('status')
                            ->label('Status Dostępności')
                            ->options([
                                CatalogItemStatus::AVAILABLE->value    => 'Dostępny',
                                CatalogItemStatus::RESERVED->value     => 'Zarezerwowany',
                                CatalogItemStatus::SOLD_ADOPTED->value => 'Sprzedany / Adoptowany',
                                CatalogItemStatus::HIDDEN->value       => 'Ukryty (Draft)',
                            ])
                            ->default(CatalogItemStatus::AVAILABLE->value)
                            ->required(),

                        TextInput::make('price_minor')
                            ->label('Cena (PLN / EUR)')
                            ->numeric()
                            ->prefix('zł')
                            ->placeholder('np. 3500.00')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format($state / 100, 2, '.', '') : null)
                            ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null)
                            ->helperText('Wprowadź kwotę standardowo. W bazie przechowywana jest w groszach (BCMath / Minor Units). Puste = "Zapytaj o cenę".'),

                        TextInput::make('whatsapp_prefill_text')
                            ->label('Wstępna wiadomość WhatsApp')
                            ->placeholder('np. Dzień dobry, pytam o dostępność kocura Apollo')
                            ->maxLength(255)
                            ->helperText('Tekst generowany automatycznie po kliknięciu przycisku "Napisz na WhatsApp" na stronie.'),

                        Textarea::make('description')
                            ->label('Opis Pozycji')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Szczegółowy opis, historia, stan, charakterystyka...'),
                    ])->columns(2),

                Section::make('Atrybuty Branżowe (Elastyczny JSON — Wzorzec Hybrydowy 3NF)')
                    ->schema([
                        KeyValue::make('attributes')
                            ->label('Specyfikacja cech')
                            ->keyLabel('Klucz cechy (np. breed, gender, color, birth_date, year, mileage)')
                            ->valueLabel('Wartość (np. Brytyjski, Samiec, Niebieski, 2024-05-10)')
                            ->reorderable()
                            ->columnSpanFull()
                            ->helperText('Zamiast sztywnych kolumn bazodanowych, cechy specyficzne dla branży są zapisywane w schemaless JSON. Eliminuje to puste kolumny w bazie danych.'),
                    ]),
            ]);
    }
}
