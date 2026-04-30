<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Enums\QuoteStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
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
                Section::make('General Info')
                    ->schema([
                        TextInput::make('quote_number')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('lead_id')
                            ->relationship('lead', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Section::make('Financials')
                    ->schema([
                        TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        DatePicker::make('valid_until')
                            ->required(),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        ToggleButtons::make('status')
                            ->options(QuoteStatus::class)
                            ->inline()
                            ->default(QuoteStatus::DRAFT)
                            ->required(),
                    ]),
            ]);
    }
}
