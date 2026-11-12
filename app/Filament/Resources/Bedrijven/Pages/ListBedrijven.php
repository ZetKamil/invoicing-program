<?php

namespace App\Filament\Resources\Bedrijven\Pages;

use App\Actions\Tenants\ProvisionTenantAction;
use App\Filament\Resources\Bedrijven\BedrijfResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBedrijven extends ListRecords
{
    protected static string $resource = BedrijfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('provision_tenant')
                ->label('🚀 Nieuwe Klant Inrichten')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Nieuw Bedrijf Inrichten (1-Klik Onboarding)')
                ->modalDescription('Maakt automatisch het bedrijf aan, koppelt de beheerder, laadt Belgische Peppol-tarieven en genereert een professionele startpagina.')
                ->modalSubmitActionLabel('Inrichten & Activeren')
                ->form([
                    TextInput::make('company_name')
                        ->label('Bedrijfsnaam')
                        ->placeholder('bijv. Jan de Smet Transport BV')
                        ->required(),
                    Select::make('niche')
                        ->label('Bedrijfssector / Niche')
                        ->options([
                            'transport' => 'Transport & Logistiek (CMR, Vloot, Offertes)',
                            'breeding' => 'Dierenfokkerij / Cattery (Nestjes, Stamboom, Adoptie)',
                            'craftsman' => 'Vakmanschap / Bouw (Realisaties, Schrijnwerk, Offertes)',
                            'blank' => 'Zelfbouw (Czysty startowy szablon)',
                        ])
                        ->default('blank')
                        ->required(),
                    Select::make('plan')
                        ->label('Abonnementspakket')
                        ->options([
                            'start' => 'Start (Zelfbouwen - €39/mc)',
                            'pro' => 'Pro (Turnkey Business - €150/mc)',
                            'scale' => 'Scale (Enterprise ERP - €350/mc)',
                        ])
                        ->default('pro')
                        ->required(),
                    TextInput::make('admin_name')
                        ->label('Naam van de Beheerder')
                        ->placeholder('bijv. Jan de Smet')
                        ->required(),
                    TextInput::make('admin_email')
                        ->label('E-mailadres Beheerder')
                        ->email()
                        ->placeholder('jan@desmet-transport.be')
                        ->required(),
                    TextInput::make('vat_number')
                        ->label('BTW-nummer (optioneel)')
                        ->placeholder('BE0123456789'),
                    TextInput::make('iban')
                        ->label('IBAN Bankrekening (optioneel)')
                        ->placeholder('BE68 .... .... ....'),
                    TextInput::make('tagline')
                        ->label('Slogan / Tagline (optioneel)')
                        ->placeholder('Uw betrouwbare partner in de regio'),
                ])
                ->action(function (array $data, ProvisionTenantAction $provisionAction) {
                    try {
                        $result = $provisionAction->execute([
                            ...$data,
                            'operator_user_id' => auth()->id(),
                            'provisioned_by' => 'human',
                        ]);

                        Notification::make()
                            ->title('Bedrijf succesvol ingericht!')
                            ->body("Bedrijf {$result['tenant']->name} is actief met startpagina en Belgische Peppol-tarieven.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Fout bij inrichten van bedrijf')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Handmatig toevoegen'),
        ];
    }
}
