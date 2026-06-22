<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('company_name')
                    ->label('Bedrijf / Naam')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('vat_number')
                    ->label('BTW-nummer')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('city')
                    ->label('Stad')
                    ->searchable(),
            ])
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
