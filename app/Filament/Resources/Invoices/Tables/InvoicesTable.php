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
            ->recordActions([
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
                EditAction::make(),
                \Filament\Actions\ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
