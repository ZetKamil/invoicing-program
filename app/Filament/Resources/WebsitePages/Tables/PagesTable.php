<?php

namespace App\Filament\Resources\WebsitePages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Paginatitel')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->label('URL Pad')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_homepage')
                    ->label('Homepage')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Gepubliceerd')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('blocks')
                    ->label('Klocken')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' klocken' : '0 klocken')
                    ->badge()
                    ->color('info'),

                TextColumn::make('updated_at')
                    ->label('Laatst gewijzigd')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                \Filament\Actions\Action::make('preview')
                    ->label('Bekijk Pagina')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (\App\Models\Page $record) => $record->is_homepage ? url('/') : url("/?page={$record->slug}"))
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
