<?php

namespace App\Filament\Resources\CatalogItems\Tables;

use App\Enums\CatalogItemStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CatalogItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Kategoria')
                    ->badge()
                    ->color('info')
                    ->placeholder('Bez kategorii')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        CatalogItemStatus::AVAILABLE->value    => 'success',
                        CatalogItemStatus::RESERVED->value     => 'warning',
                        CatalogItemStatus::SOLD_ADOPTED->value => 'gray',
                        CatalogItemStatus::HIDDEN->value       => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state?->value ?? $state) {
                        CatalogItemStatus::AVAILABLE->value    => 'Dostępny',
                        CatalogItemStatus::RESERVED->value     => 'Zarezerwowany',
                        CatalogItemStatus::SOLD_ADOPTED->value => 'Sprzedany / Adoptowany',
                        CatalogItemStatus::HIDDEN->value       => 'Ukryty',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('price_minor')
                    ->label('Cena')
                    ->formatStateUsing(fn ($record) => $record->price_formatted ? $record->price_formatted . ' zł' : 'Zapytaj o cenę')
                    ->sortable(),

                TextColumn::make('attributes')
                    ->label('Atrybuty (JSON)')
                    ->formatStateUsing(function ($state) {
                        if (! is_array($state) || empty($state)) {
                            return '-';
                        }
                        return collect($state)
                            ->map(fn ($val, $key) => "{$key}: {$val}")
                            ->take(3)
                            ->implode(' | ');
                    })
                    ->limit(45),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        CatalogItemStatus::AVAILABLE->value    => 'Dostępny',
                        CatalogItemStatus::RESERVED->value     => 'Zarezerwowany',
                        CatalogItemStatus::SOLD_ADOPTED->value => 'Sprzedany / Adoptowany',
                        CatalogItemStatus::HIDDEN->value       => 'Ukryty',
                    ]),

                SelectFilter::make('category_id')
                    ->label('Kategoria')
                    ->relationship('category', 'name', fn ($query) => $query->where('type', 'catalog')),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
