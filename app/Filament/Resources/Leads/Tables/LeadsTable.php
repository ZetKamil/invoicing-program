<?php

namespace App\Filament\Resources\Leads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Bedrijfsnaam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_person')
                    ->label('Contactpersoon')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-mailadres')
                    ->copyable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Aanvraagdatum')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('create_quote')
                    ->label('Maak Offerte aan')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn (\App\Models\Lead $record) => in_array($record->status, [\App\Enums\LeadStatus::NEW, \App\Enums\LeadStatus::AUDITED]))
                    ->action(function (\App\Models\Lead $record, \App\Actions\Quotes\CreateQuoteAction $createQuoteAction) {
                        if ($record->bedrijf_id !== auth()->user()->bedrijf_id) {
                            abort(403, 'Ongeautoriseerde actie.');
                        }
                        
                        $quote = $createQuoteAction->handle($record, 14);
                        
                        $record->update(['status' => \App\Enums\LeadStatus::CONVERTED]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Offerte succesvol aangemaakt vanuit Aanvraag!')
                            ->success()
                            ->send();
                            
                        return redirect()->to(\App\Filament\Resources\Quotes\QuoteResource::getUrl('edit', ['record' => $quote->id]));
                    }),
                \Filament\Actions\Action::make('convert_to_customer')
                    ->label('Maak Klant aan')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Klant aanmaken')
                    ->modalDescription('Weet je zeker dat je van deze aanvraag een vaste klant wilt maken?')
                    ->action(function (\App\Models\Lead $record) {
                        $customer = \App\Models\Customer::where('bedrijf_id', $record->bedrijf_id)
                            ->where(function($q) use ($record) {
                                $q->where('company_name', $record->company_name)
                                  ->orWhere('email', $record->email);
                            })->first();

                        if (!$customer) {
                            \App\Models\Customer::create([
                                'bedrijf_id' => $record->bedrijf_id,
                                'company_name' => $record->company_name,
                                'contact_person' => $record->contact_person,
                                'email' => $record->email,
                                'phone' => $record->phone,
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Klant succesvol aangemaakt!')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Deze klant (Bedrijfsnaam of e-mail) bestaat al in het systeem.')
                                ->warning()
                                ->send();
                        }
                    }),
                \Filament\Actions\Action::make('reject')
                    ->label('Wijzen af')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (\App\Models\Lead $record) => in_array($record->status, [\App\Enums\LeadStatus::NEW, \App\Enums\LeadStatus::AUDITED, \App\Enums\LeadStatus::CONTACTED]))
                    ->action(function (\App\Models\Lead $record) {
                        $record->update(['status' => \App\Enums\LeadStatus::REJECTED]);
                        \Filament\Notifications\Notification::make()
                            ->title('Aanvraag succesvol afgewezen.')
                            ->success()
                            ->send();
                    }),
                EditAction::make()->slideOver(),
            ])
            ->bulkActions([
                // Removed DeleteBulkAction to preserve audit history
            ]);
    }
}
