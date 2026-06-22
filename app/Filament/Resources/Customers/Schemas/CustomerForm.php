<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Bedrijfsgegevens')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('company_name')
                            ->label('Bedrijfsnaam / Klantnaam')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('vat_number')
                            ->label('BTW-nummer (Optioneel)')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('contact_person')
                            ->label('Contactpersoon')
                            ->maxLength(255),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Contact & Adres')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('E-mailadres')
                            ->email()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->label('Telefoonnummer')
                            ->tel()
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('address')
                            ->label('Straat en Huisnummer')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        \Filament\Forms\Components\TextInput::make('zip_code')
                            ->label('Postcode')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('city')
                            ->label('Stad')
                            ->maxLength(255),
                        \Filament\Forms\Components\TextInput::make('country')
                            ->label('Land')
                            ->default('België')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }
}
