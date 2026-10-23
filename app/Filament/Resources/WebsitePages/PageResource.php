<?php

namespace App\Filament\Resources\WebsitePages;

use App\Filament\Resources\WebsitePages\Pages\CreatePage;
use App\Filament\Resources\WebsitePages\Pages\EditPage;
use App\Filament\Resources\WebsitePages\Pages\ListPages;
use App\Filament\Resources\WebsitePages\Schemas\PageForm;
use App\Filament\Resources\WebsitePages\Tables\PagesTable;
use App\Models\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|\UnitEnum|null $navigationGroup = 'CMS & Website';

    protected static ?string $modelLabel = 'Pagina';

    protected static ?string $pluralModelLabel = 'Website Pagina\'s';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
