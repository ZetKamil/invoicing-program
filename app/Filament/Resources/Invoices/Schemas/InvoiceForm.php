<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
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

                Section::make('Status & Payment')
                    ->schema([
                        ToggleButtons::make('status')
                            ->options(InvoiceStatus::class)
                            ->inline()
                            ->default(InvoiceStatus::DRAFT)
                            ->required(),
                            
                        TextInput::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent ID')
                            ->disabled()
                            ->helperText('Filled automatically when paid via Stripe webhook.'),
                            
                        \Filament\Forms\Components\Placeholder::make('payment_link')
                            ->label('Public Payment Link')
                            ->content(function (?\App\Models\Invoice $record) {
                                if (!$record || !$record->id) return '-';
                                $url = route('invoice.pay', ['invoice' => $record->id]);
                                return new \Illuminate\Support\HtmlString("<a href=\"{$url}\" target=\"_blank\" class=\"text-blue-600 underline\">{$url}</a>");
                            }),
                    ])->columns(3),
            ]);
    }
}
