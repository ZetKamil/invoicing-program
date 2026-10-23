<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Factuurnummer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quote.quote_number')
                    ->label('Bron Offerte')
                    ->searchable()
                    ->sortable()
                    ->url(fn (\App\Models\Invoice $record): ?string => $record->quote_id ? \App\Filament\Resources\Quotes\QuoteResource::getUrl('edit', ['record' => $record->quote_id]) : null)
                    ->color('primary')
                    ->placeholder('-'),

                TextColumn::make('customer.company_name')
                    ->label('Klant / Aanvraag')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('send_invoice')
                        ->label('Verstuur Factuur & Betaallink')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Factuur verzenden naar klant')
                        ->modalDescription('Er wordt een e-mail verzonden met de PDF-factuur en een directe online betaallink via Bancontact / Kaart.')
                        ->modalSubmitActionLabel('Verstuur Nu')
                        ->action(function (\App\Models\Invoice $record, \App\Actions\Invoices\SendCommercialDocumentAction $sendAction) {
                            try {
                                $sendAction->execute($record, operatorUserId: auth()->id());
                                \Filament\Notifications\Notification::make()
                                    ->title('Factuur & Betaallink verzonden!')
                                    ->body("De factuur is succesvol verzonden naar {$record->customer?->email}.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Fout bij verzenden')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    \Filament\Actions\Action::make('copy_payment_link')
                        ->label('Kopieer Betaallink')
                        ->icon('heroicon-o-link')
                        ->color('info')
                        ->action(function (\App\Models\Invoice $record) {
                            if (empty($record->payment_token)) {
                                $record->payment_token = \Illuminate\Support\Str::random(40);
                                $record->saveQuietly();
                            }
                            $url = url("/pay/{$record->payment_token}");
                            \Filament\Notifications\Notification::make()
                                ->title('Online Betaallink')
                                ->body($url)
                                ->info()
                                ->persistent()
                                ->send();
                        }),

                    \Filament\Actions\Action::make('mark_paid')
                        ->label('Markeer als Betaald')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (\App\Models\Invoice $record) => ($record->status?->value ?? $record->status) !== 'paid')
                        ->requiresConfirmation()
                        ->modalHeading('Factuur markeren als voldaan?')
                        ->modalDescription('De status wordt gewijzigd naar Betaald en de betaaldatum wordt ingesteld op vandaag.')
                        ->action(function (\App\Models\Invoice $record) {
                            $record->update([
                                'status' => 'paid',
                                'paid_at' => now(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Factuur gemarkeerd als betaald')
                                ->success()
                                ->send();
                        }),

                    \Filament\Actions\Action::make('download_pdf')
                        ->label('Download Voorbeeld (PDF/HTML)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->action(function (\App\Models\Invoice $record) {
                            $html = \Illuminate\Support\Facades\View::make('invoices.pdf', [
                                'invoice' => $record->load(['tenant', 'customer', 'items'])
                            ])->render();

                            return response()->streamDownload(function () use ($html) {
                                echo $html;
                            }, $record->invoice_number . '.html', [
                                'Content-Type' => 'text/html',
                            ]);
                        }),
                    \Filament\Actions\Action::make('generate_ubl')
                        ->label('Genereer Peppol UBL')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (\App\Models\Invoice $record, \App\Actions\Invoices\GenerateUblXmlAction $generateAction) {
                            try {
                                $path = $generateAction->execute($record);
                                $record->update(['ubl_xml_path' => $path]);
                                \Filament\Notifications\Notification::make()->title('Peppol UBL gegenereerd!')->success()->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()->title('Fout bij genereren UBL: ' . $e->getMessage())->danger()->send();
                            }
                        }),
                    \Filament\Actions\Action::make('download_ubl')
                        ->label('Download Peppol UBL')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->visible(fn (\App\Models\Invoice $record) => !empty($record->ubl_xml_path))
                        ->action(function (\App\Models\Invoice $record) {
                            return response()->streamDownload(function () use ($record) {
                                echo \Illuminate\Support\Facades\Storage::get($record->ubl_xml_path);
                            }, $record->invoice_number . '-peppol.xml', [
                                'Content-Type' => 'application/xml',
                                'Content-Disposition' => 'attachment; filename="'.$record->invoice_number.'-peppol.xml"',
                            ]);
                        }),
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\ViewAction::make(),
                ])
                ->tooltip('Acties')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
