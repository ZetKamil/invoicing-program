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
                ->label('Convert to Invoice')
                ->icon('heroicon-o-document-currency-euro')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (\App\Models\Quote $record) => in_array($record->status, [\App\Enums\QuoteStatus::SENT, \App\Enums\QuoteStatus::ACCEPTED]))
                ->action(function (\App\Models\Quote $record, \App\Actions\Invoices\CreateInvoiceFromQuoteAction $createInvoiceAction, \App\Actions\Invoices\GenerateInvoicePdfAction $generatePdfAction) {
                    if ($record->tenant_id !== auth()->user()->tenant_id) {
                        abort(403, 'Unauthorized action.');
                    }

                    // Create Invoice
                    $invoice = $createInvoiceAction->execute($record);

                    // Generate PDF (HTML placeholder)
                    $generatePdfAction->execute($invoice);

                    // Update Quote status
                    $record->update(['status' => \App\Enums\QuoteStatus::CONVERTED]);

                    \Filament\Notifications\Notification::make()
                        ->title('Quote converted to Invoice successfully!')
                        ->success()
                        ->send();

                    return redirect()->to(\App\Filament\Resources\Invoices\InvoiceResource::getUrl('edit', ['record' => $invoice->id]));
                }),
            \Filament\Actions\Action::make('send_quote')
                ->label('Send Quote via Email')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (\App\Models\Quote $record, \App\Actions\Communications\LogCommunicationAction $logCommunicationAction) {
                    if ($record->tenant_id !== auth()->user()->tenant_id) {
                        abort(403, 'Unauthorized action.');
                    }

                    // Ensure lead email exists
                    if (!$record->lead || !$record->lead->email) {
                        \Filament\Notifications\Notification::make()->title('Lead has no email address.')->danger()->send();
                        return;
                    }

                    // Send email
                    \Illuminate\Support\Facades\Mail::to($record->lead->email)->send(new \App\Mail\QuoteInquiryMail($record));

                    // Log communication
                    $subject = 'Your Quote from ' . $record->tenant->name;
                    $logCommunicationAction->execute($record->tenant_id, $subject, $record, \App\Enums\CommType::EMAIL);

                    // Update status
                    $record->update(['status' => \App\Enums\QuoteStatus::SENT]);

                    \Filament\Notifications\Notification::make()
                        ->title('Quote sent and communication logged successfully!')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
