<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
        ];
    }
}
