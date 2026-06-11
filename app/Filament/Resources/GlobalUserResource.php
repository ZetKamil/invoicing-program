<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlobalUserResource\Pages;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use STS\FilamentImpersonate\Actions\Impersonate;
use Illuminate\Database\Eloquent\Builder;

class GlobalUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Super Admin';
    
    protected static ?string $modelLabel = 'Bedrijf User';
    
    protected static ?int $navigationSort = 100;

    public static function canViewAny(): bool
    {
        return auth()->user()->is_super_admin ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('bedrijf.name')->label('Bedrijf')->searchable(),
                TextColumn::make('role')->badge(),
            ])
            ->filters([])
            ->actions([
                Impersonate::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlobalUsers::route('/'),
        ];
    }
}
