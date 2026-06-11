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
