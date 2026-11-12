<?php

namespace App\Filament\Resources\Bedrijven\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BedrijvenTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('plan')
                    ->label('Plan')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'scale' => 'success',
                        'pro' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('custom_domain')
                    ->label('Eigen Domein')
                    ->searchable(),
                TextColumn::make('vat_number')
                    ->searchable(),
                TextColumn::make('peppol_id')
                    ->searchable(),
                TextColumn::make('stripe_id')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stripe_subscription_id')
                    ->searchable(),
                TextColumn::make('stripe_subscription_status')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('switch_tenant')
                    ->label('Switch Context')
                    ->icon('heroicon-o-arrow-right-end-on-rectangle')
                    ->color('info')
                    ->action(function (\App\Models\Bedrijf $record) {
                        session(['filament_tenant_id' => $record->id]);
                        \Filament\Notifications\Notification::make()
                            ->title("Overgeschakeld naar context van {$record->name}")
                            ->success()
                            ->send();

                        return redirect()->to('/admin');
                    }),

                \Filament\Actions\Action::make('view_site')
                    ->label('Bekijk Website')
                    ->icon('heroicon-o-globe-alt')
                    ->color('gray')
                    ->url(fn (\App\Models\Bedrijf $record) => url("/?tenant={$record->slug}"))
                    ->openUrlInNewTab(),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
