<?php

namespace App\Filament\Resources\Quotes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Offertenummer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lead.company_name')
                    ->label('Aanvraag / Klant')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Totaalbedrag')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('valid_until')
                    ->label('Geldig tot')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
    
                        if (!$record->lead || !$record->lead->email) {
                            \Filament\Notifications\Notification::make()->title('Aanvraag heeft geen e-mailadres.')->danger()->send();
                            return;
                        }
    
                        \Illuminate\Support\Facades\Mail::to($record->lead->email)->send(new \App\Mail\QuoteInquiryMail($record));
    
                        $subject = 'Your Quote from ' . $record->bedrijf->name;
                        $logCommunicationAction->execute($record->bedrijf_id, $subject, $record, \App\Enums\CommType::EMAIL);
    
                        $record->update(['status' => \App\Enums\QuoteStatus::SENT]);
    
                        \Filament\Notifications\Notification::make()
                            ->title('Offerte verstuurd en gelogd!')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
