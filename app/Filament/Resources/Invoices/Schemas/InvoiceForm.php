<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Details')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->required()
                            ->unique(ignoreRecord: true),

                        DatePicker::make('due_date')
                            ->required(),
                    ])->columns(2),

                Section::make('Totals')
                    ->schema([
                        TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('tax_total')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                    ])->columns(3),

                Section::make('Peppol / Legal')
                    ->schema([
                        TextInput::make('buyer_reference')
                            ->maxLength(255),

                        TextInput::make('ubl_xml_path')
                            ->label('UBL XML Path')
                            ->disabled(),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        ToggleButtons::make('status')
                            ->options(InvoiceStatus::class)
                            ->inline()
                            ->default(InvoiceStatus::DRAFT)
                            ->required(),
                    ]),
            ]);
    }
}
