<?php

namespace App\Filament\Resources\Bedrijven\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BedrijfForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bedrijf Details')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('slug')
                            ->required(),
                        TextInput::make('vat_number'),
                        TextInput::make('peppol_id'),
                        TextInput::make('stripe_id'),
                        Textarea::make('settings')
                            ->columnSpanFull(),
                        CheckboxList::make('active_packages')
                            ->options([
                                '1' => 'Webdesign: Basic Setup',
                                '2' => 'Webdesign: Pro Transport Edition',
                                '3' => 'Master-Digit Core: Invoicing & Peppol XML',
                                '4' => 'Extension: Truck GPS Tracking',
                                '5' => 'Extension: Delivery Monitoring',
                                '6' => 'Extension: Fleet Management (TMS)',
                                '7' => 'Extension: Products Management',
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        TextInput::make('stripe_subscription_id'),
                        TextInput::make('stripe_subscription_status'),
                    ])->columns(2),

                Section::make('Primary Administrator')
                    ->schema([
                        TextInput::make('admin_email')
                            ->email()
                            ->required(),
                        TextInput::make('admin_password')
                            ->password()
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
