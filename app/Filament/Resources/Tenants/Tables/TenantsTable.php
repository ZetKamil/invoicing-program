<?php

namespace App\Filament\Resources\Tenants\Tables;

use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenantsTable
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
                TextColumn::make('peppol_endpoint_id')
                    ->label('Peppol ID')
                    ->searchable(),
                TextColumn::make('stripe_customer_id')
                    ->label('Stripe ID')
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
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
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
                    ->action(function (Tenant $record) {
                        session(['current_tenant_id' => $record->id]);
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
                    ->url(fn (Tenant $record) => url("/?tenant={$record->slug}"))
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
