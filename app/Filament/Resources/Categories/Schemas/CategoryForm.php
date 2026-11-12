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
                Section::make('Categoriegegevens')
                    ->schema([
                        TextInput::make('name')
                            ->label('Categorienaam')
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
                            ->helperText('Unieke identificatie in de URL, bijv. kittens-te-koop'),

                        Select::make('type')
                            ->label('Bestemming (Type)')
                            ->options([
                                CategoryType::CATALOG->value => 'Productcatalogus',
                                CategoryType::POST->value    => 'Blogberichten',
                            ])
                            ->default(CategoryType::CATALOG->value)
                            ->required()
                            ->helperText('Bepaalt of de categorie wordt gebruikt voor catalogusartikelen of blogberichten.'),
                    ])->columns(2),
            ]);
    }
}
