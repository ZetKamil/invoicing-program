<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadStatus;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bedrijfsgegevens')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Bedrijfsnaam')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('contact_person')
                            ->label('Contactpersoon')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Contactgegevens')
                    ->schema([
                        TextInput::make('email')
                            ->label('E-mailadres')
                            ->email()
                            ->required(),

                        TextInput::make('phone')
                            ->label('Telefoonnummer')
                            ->tel(),
                    ])->columns(2),

                Section::make('Transportgegevens (Van Website)')
                    ->schema([
                        TextInput::make('metadata.trailer_type')
                            ->label('Type Trailer')
                            ->disabled()
                            ->columnSpan(1),
                            
                        TextInput::make('metadata.cargo_weight_kg')
                            ->label('Bruto Gewicht (kg)')
                            ->disabled()
                            ->columnSpan(1),
                            
                        TextInput::make('metadata.pallet_count')
                            ->label('Aantal Pallets')
                            ->disabled()
                            ->columnSpan(1),
                            
                        TextInput::make('metadata.package')
                            ->label('Gevraagd Pakket')
                            ->disabled()
                            ->columnSpan(1),
                            
                        TextInput::make('metadata.loading_address')
                            ->label('Laadadres')
                            ->disabled()
                            ->columnSpan(2),
                            
                        TextInput::make('metadata.loading_date')
                            ->label('Laaddatum')
                            ->disabled()
                            ->columnSpan(2),
                            
                        TextInput::make('metadata.delivery_address')
                            ->label('Losadres')
                            ->disabled()
                            ->columnSpan(2),
                            
                        TextInput::make('metadata.delivery_date')
                            ->label('Losdatum')
                            ->disabled()
                            ->columnSpan(2),
                    ])->columns(4),

                Section::make('Bericht / Opmerkingen')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('metadata.message')
                            ->label('Bericht van Klant')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & Extra')
                    ->schema([
                        ToggleButtons::make('status')
                            ->label('Status')
                            ->options(LeadStatus::class)
                            ->inline()
                            ->default(LeadStatus::NEW)
                            ->required(),

                        TextInput::make('audit_report_url')
                            ->url()
                            ->label('Audit Rapport URL'),
                    ])->columns(2),
            ]);
    }
}
