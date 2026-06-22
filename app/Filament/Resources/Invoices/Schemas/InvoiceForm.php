<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Factuurgegevens')
                    ->schema([
                        \Filament\Forms\Components\MorphToSelect::make('customer')
                            ->label('Klant / Afnemer')
                            ->types([
                                \Filament\Forms\Components\MorphToSelect\Type::make(\App\Models\Customer::class)
                                    ->titleAttribute('company_name')
                                    ->label('Bestaande Klant'),
                                \Filament\Forms\Components\MorphToSelect\Type::make(\App\Models\Lead::class)
                                    ->titleAttribute('company_name')
                                    ->label('Lead / Aanvraag'),
                            ])
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('invoice_number')
                            ->label('Factuurnummer')
                            ->required()
                            ->unique(ignoreRecord: true),

                        DatePicker::make('due_date')
                            ->label('Vervaldatum')
                            ->required(),

                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Opmerkingen / Voorwaarden')
                            ->columnSpanFull(),
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
                            
                        TextInput::make('cmr_number')
                            ->label('CMR Number')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('truck_license_plate')
                            ->label('Kenteken Vrachtwagen')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('trailer_license_plate')
                            ->label('Kenteken Trailer')
                            ->maxLength(255)
                            ->columnSpan(1),
                            
                        TextInput::make('driver_name')
                            ->label('Naam Chauffeur')
                            ->maxLength(255)
                            ->columnSpan(1),
                            
                        TextInput::make('driver_phone')
                            ->label('Telefoonnummer Chauffeur')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('cargo_weight_kg')
                            ->label('Bruto Gewicht (kg)')
                            ->numeric()
                            ->columnSpan(2),

                        TextInput::make('pallet_count')
                            ->label('Aantal Pallets')
                            ->numeric()
                            ->columnSpan(2),
                            
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

                Section::make('Totalen & BTW')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotaal')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('tax_total')
                            ->label('Totaal BTW')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('total_amount')
                            ->label('Totaalbedrag')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                            
                        Toggle::make('is_reverse_charge')
                            ->label('Btw verlegd (Reverse Charge)')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $set('tax_total', 0);
                                    $set('total_amount', $get('subtotal'));
                                    $notes = $get('notes') ?? '';
                                    if (!str_contains($notes, 'Vrijgesteld van btw - Btw verlegd')) {
                                        $set('notes', trim($notes . "\n\nVrijgesteld van btw - Btw verlegd"));
                                    }
                                }
                            })
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Documenten & Peppol')
                    ->schema([
                        FileUpload::make('cmr_document_path')
                            ->label('CMR / Vrachtbrief (PDF/JPG)')
                            ->disk('public')
                            ->directory('cmr-documents')
                            ->columnSpanFull(),

                        TextInput::make('buyer_reference')
                            ->label('Koper Referentie')
                            ->maxLength(255),

                        TextInput::make('ubl_xml_path')
                            ->label('UBL XML Pad')
                            ->disabled(),
                    ])->columns(2),

                Section::make('Status & Betaling')
                    ->schema([
                        ToggleButtons::make('status')
                            ->label('Status')
                            ->options(InvoiceStatus::class)
                            ->inline()
                            ->default(InvoiceStatus::DRAFT)
                            ->required(),
                            
                        TextInput::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent ID')
                            ->disabled()
                            ->helperText('Automatisch ingevuld via Stripe webhook na betaling.'),
                            
                        \Filament\Forms\Components\Placeholder::make('payment_link')
                            ->label('Publieke Betaallink')
                            ->content(function (?\App\Models\Invoice $record) {
                                if (!$record || !$record->id) return '-';
                                $url = route('invoice.pay', ['invoice' => $record->id]);
                                return new \Illuminate\Support\HtmlString("<a href=\"{$url}\" target=\"_blank\" class=\"text-blue-600 underline\">{$url}</a>");
                            }),
                            
                        \Filament\Forms\Components\Placeholder::make('quote_link')
                            ->label('Bron Offerte')
                            ->content(function (?\App\Models\Invoice $record) {
                                if (!$record || !$record->quote_id) return 'Geen gekoppelde offerte';
                                $url = \App\Filament\Resources\Quotes\QuoteResource::getUrl('edit', ['record' => $record->quote_id]);
                                $number = $record->quote->quote_number ?? 'Bekijk Offerte';
                                return new \Illuminate\Support\HtmlString("<a href=\"{$url}\" class=\"text-blue-600 underline font-bold\">{$number}</a>");
                            }),
                    ])->columns(4),
            ]);
    }
}
