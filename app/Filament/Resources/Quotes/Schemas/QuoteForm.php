<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Enums\QuoteStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Algemene Informatie')
                    ->schema([
                        TextInput::make('quote_number')
                            ->label('Offertenummer')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        Select::make('lead_id')
                            ->label('Aanvraag (Lead)')
                            ->relationship('lead', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Begeleidend Schrijven / Beschrijving')
                            ->columnSpanFull(),
                            
                        \Filament\Forms\Components\KeyValue::make('metadata')
                            ->label('Speciale Transportvereisten (uit Aanvraag)')
                            ->columnSpanFull()
                            ->addActionLabel('Vereiste toevoegen')
                            ->keyLabel('Vereiste / Veld')
                            ->valueLabel('Details'),
                    ])->columns(2),

                Section::make('Transportgegevens')
                    ->schema([
                        Select::make('incoterms')
                            ->label('Incoterms')
                            ->options([
                                'EXW' => 'EXW (Ex Works)',
                                'FCA' => 'FCA (Free Carrier)',
                                'CPT' => 'CPT (Carriage Paid To)',
                                'CIP' => 'CIP (Carriage and Insurance Paid To)',
                                'DAP' => 'DAP (Delivered at Place)',
                                'DPU' => 'DPU (Delivered at Place Unloaded)',
                                'DDP' => 'DDP (Delivered Duty Paid)',
                            ])
                            ->columnSpan(1),
                            
                        Select::make('trailer_type')
                            ->label('Type Trailer')
                            ->options([
                                'Huifwagen' => 'Huifwagen',
                                'Koelwagen' => 'Koelwagen',
                                'Container' => 'Container',
                                'Dieplader' => 'Dieplader',
                                'Silo' => 'Silo',
                                'Kipper' => 'Kipper',
                                'Tankwagen' => 'Tankwagen',
                            ])->columnSpan(1),
                        
                        TextInput::make('cargo_weight_kg')
                            ->label('Bruto Gewicht (kg)')
                            ->numeric()
                            ->columnSpan(1),

                        TextInput::make('pallet_count')
                            ->label('Aantal Pallets')
                            ->numeric()
                            ->columnSpan(1),

                        TextInput::make('loading_address')
                            ->label('Laadadres (Volledig)')
                            ->columnSpan(2),
                        DatePicker::make('loading_date')
                            ->label('Laaddatum')
                            ->columnSpan(2),

                        TextInput::make('delivery_address')
                            ->label('Losadres (Volledig)')
                            ->columnSpan(2),
                        DatePicker::make('delivery_date')
                            ->label('Losdatum')
                            ->columnSpan(2),

                    ])->columns(4),

                Section::make('Financiën')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Totaalbedrag')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        DatePicker::make('valid_until')
                            ->label('Geldig tot')
                            ->required(),
                    ])->columns(2),

                Section::make('Regels / Items')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->relationship()
                            ->schema([
                                TextInput::make('description')
                                    ->label('Omschrijving')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Aantal')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('unit_price')
                                    ->label('Stukprijs')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0.00)
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('tax_rate')
                                    ->label('BTW (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(21.00)
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('total')
                                    ->label('Totaal')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0.00)
                                    ->disabled()
                                    ->columnSpan(1)
                                    ->helperText('Auto-berekend.'),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Item toevoegen'),
                    ]),

                Section::make('Status')
                    ->schema([
                        ToggleButtons::make('status')
                            ->label('Status')
                            ->options(QuoteStatus::class)
                            ->inline()
                            ->default(QuoteStatus::DRAFT)
                            ->required(),
                    ]),
            ]);
    }
}
