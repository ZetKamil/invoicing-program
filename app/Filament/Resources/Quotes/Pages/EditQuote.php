<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('convert_to_invoice')
                ->label('Zet om naar Factuur')
                ->icon('heroicon-o-document-currency-euro')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (\App\Models\Quote $record) => in_array($record->status, [\App\Enums\QuoteStatus::SENT, \App\Enums\QuoteStatus::ACCEPTED]))
                ->action(function (\App\Models\Quote $record, \App\Actions\Invoices\CreateInvoiceFromQuoteAction $createInvoiceAction, \App\Actions\Invoices\GenerateInvoicePdfAction $generatePdfAction) {
                    if ($record->bedrijf_id !== auth()->user()->bedrijf_id) {
                        abort(403, 'Ongeautoriseerde actie.');
                    }

                    // Create Invoice
                    $invoice = $createInvoiceAction->execute($record);

                    // Generate PDF (HTML placeholder)
                    $generatePdfAction->execute($invoice);

                    // Update Quote status
                    $record->update(['status' => \App\Enums\QuoteStatus::CONVERTED]);

                    \Filament\Notifications\Notification::make()
                        ->title('Offerte succesvol omgezet naar Factuur!')
                        ->success()
                        ->send();

                    return redirect()->to(\App\Filament\Resources\Invoices\InvoiceResource::getUrl('edit', ['record' => $invoice->id]));
                }),
            \Filament\Actions\Action::make('send_quote')
                ->label('Verstuur Offerte via E-mail')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (\App\Models\Quote $record, \App\Actions\Communications\LogCommunicationAction $logCommunicationAction) {
                    if ($record->bedrijf_id !== auth()->user()->bedrijf_id) {
                        abort(403, 'Ongeautoriseerde actie.');
                    }

                    // Ensure lead email exists
                    if (!$record->lead || !$record->lead->email) {
                        \Filament\Notifications\Notification::make()->title('Aanvraag heeft geen e-mailadres.')->danger()->send();
                        return;
                    }

                    // Queue the email — QuoteInquiryMail implements ShouldQueue, so we must
                    // use ->queue() (not ->send()) to avoid blocking the HTTP worker thread.
                    \Illuminate\Support\Facades\Mail::to($record->lead->email)->queue(new \App\Mail\QuoteInquiryMail($record));

                    // Log communication
                    $subject = 'Your Quote from ' . $record->bedrijf->name;
                    $body = 'Automated system email sent to ' . $record->lead->email . ' containing the quote.';
                    $logCommunicationAction->execute($record->bedrijf_id, $subject, $record, \App\Enums\CommType::EMAIL, $body);

                    // Update status
                    $record->update(['status' => \App\Enums\QuoteStatus::SENT]);

                    \Filament\Notifications\Notification::make()
                        ->title('Offerte verstuurd en gelogd!')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
