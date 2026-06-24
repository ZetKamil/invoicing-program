<?php

namespace App\Filament\Resources\Bedrijven;

use App\Filament\Resources\Bedrijven\Pages\CreateBedrijf;
use App\Filament\Resources\Bedrijven\Pages\EditBedrijf;
use App\Filament\Resources\Bedrijven\Pages\ListBedrijven;
use App\Filament\Resources\Bedrijven\Schemas\BedrijfForm;
use App\Filament\Resources\Bedrijven\Tables\BedrijvenTable;
use App\Models\Bedrijf;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BedrijfResource extends Resource
{
    protected static ?string $model = Bedrijf::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Super Admin';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return auth()->user()->is_super_admin ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return BedrijfForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BedrijvenTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBedrijven::route('/'),
            'create' => CreateBedrijf::route('/create'),
            'edit' => EditBedrijf::route('/{record}/edit'),
        ];
    }
}
