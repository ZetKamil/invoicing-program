<?php

namespace App\Filament\Resources\Bedrijven\Schemas;

use App\Enums\TenantPlan;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                        Select::make('plan')
                            ->label('Abonnement / SaaS Plan')
                            ->options([
                                TenantPlan::START->value => 'Start (Catalogus + Blog, geen ERP)',
                                TenantPlan::PRO->value   => 'Pro (Catalogus + Blog + Facturatie)',
                                TenantPlan::SCALE->value => 'Scale (Volledig ERP + Peppol UBL)',
                            ])
                            ->default(TenantPlan::START->value)
                            ->required(),
                        TextInput::make('custom_domain')
                            ->label('Eigen Domein')
                            ->placeholder('uwdomein.nl'),
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
