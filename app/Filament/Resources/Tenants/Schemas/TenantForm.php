<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Enums\TenantPlan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
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
                            ->placeholder('uwdomein.be'),
                        TextInput::make('vat_number')
                            ->label('BTW / KBO Nummer'),
                        TextInput::make('peppol_endpoint_id')
                            ->label('Peppol Endpoint ID'),
                        TextInput::make('iban')
                            ->label('IBAN Bankrekening'),
                        TextInput::make('bic')
                            ->label('BIC Code'),
                        TextInput::make('stripe_customer_id')
                            ->label('Stripe Klant ID'),
                        TextInput::make('stripe_subscription_id')
                            ->label('Stripe Abonnement ID'),
                    ])->columns(2),

                Section::make('Betalingsgateways & Stripe Koppeling')
                    ->description('Configureer de dedicated Stripe-sleutels van deze onderneming voor rechtstreekse Bancontact en kaartbetalingen.')
                    ->schema([
                        TextInput::make('settings.payment_gateways.stripe.publishable_key')
                            ->label('Stripe Publishable Key (pk_...)')
                            ->placeholder('pk_live_... of pk_test_...')
                            ->helperText('Publieke sleutel voor de browser checkout.'),
                        TextInput::make('settings.payment_gateways.stripe.secret_key')
                            ->label('Stripe Secret Key (sk_...)')
                            ->password()
                            ->revealable()
                            ->placeholder('sk_live_... of sk_test_...')
                            ->helperText('Geheime API-sleutel van de klant.'),
                        TextInput::make('settings.payment_gateways.stripe.webhook_secret')
                            ->label('Stripe Webhook Secret (whsec_...)')
                            ->password()
                            ->revealable()
                            ->placeholder('whsec_...'),
                    ])->columns(3),

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
