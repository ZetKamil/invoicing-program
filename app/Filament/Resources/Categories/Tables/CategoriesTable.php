<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Enums\CategoryType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        CategoryType::CATALOG->value => 'info',
                        CategoryType::POST->value    => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state?->value ?? $state) {
                        CategoryType::CATALOG->value => 'Katalog',
                        CategoryType::POST->value    => 'Blog',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('catalog_items_count')
                    ->counts('catalogItems')
                    ->label('Pozycji w katalogu')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Filtruj wg typu')
                    ->options([
                        CategoryType::CATALOG->value => 'Katalog',
                        CategoryType::POST->value    => 'Blog',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
