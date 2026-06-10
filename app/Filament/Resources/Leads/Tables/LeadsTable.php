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
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_person')
                    ->searchable(),

                TextColumn::make('email')
                    ->copyable(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('create_quote')
                    ->label('Create Quote')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn (\App\Models\Lead $record) => in_array($record->status, [\App\Enums\LeadStatus::NEW, \App\Enums\LeadStatus::AUDITED]))
                    ->action(function (\App\Models\Lead $record, \App\Actions\Quotes\CreateQuoteAction $createQuoteAction) {
                        if ($record->tenant_id !== auth()->user()->tenant_id) {
                            abort(403, 'Unauthorized action.');
                        }
                        
                        $package = $record->metadata['package'] ?? 'unknown';
                        $amount = match(strtolower($package)) {
                            'start' => 99.00,
                            'pro' => 199.00,
                            'enterprise' => 0.00,
                            default => 500.00,
                        };

                        $quote = $createQuoteAction->handle($record, $amount, 14);
                        
                        $record->update(['status' => \App\Enums\LeadStatus::CONVERTED]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Quote successfully generated from Lead!')
                            ->success()
                            ->send();
                            
                        return redirect()->to(\App\Filament\Resources\Quotes\QuoteResource::getUrl('edit', ['record' => $quote->id]));
                    }),
                EditAction::make()->slideOver(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
