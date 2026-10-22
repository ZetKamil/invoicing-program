<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CategoryType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacje o Kategorii')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nazwa Kategorii')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Identyfikator w adresie URL, np. koty-brytyjskie'),

                        Select::make('type')
                            ->label('Przeznaczenie (Typ)')
                            ->options([
                                CategoryType::CATALOG->value => 'Katalog Pozycji (Produkty / Koty / Usługi)',
                                CategoryType::POST->value    => 'Blog (Wpisy / Artykuły)',
                            ])
                            ->default(CategoryType::CATALOG->value)
                            ->required()
                            ->helperText('Wzorzec Single Table: kategoria może służyć do katalogu lub bloga.'),
                    ])->columns(2),
            ]);
    }
}
