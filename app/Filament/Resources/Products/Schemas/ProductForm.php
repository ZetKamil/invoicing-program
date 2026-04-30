<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductType;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('price')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        ToggleButtons::make('type')
                            ->options(ProductType::class)
                            ->inline()
                            ->required(),

                        Toggle::make('is_recurring')
                            ->label('Recurring Subscription')
                            ->default(false),

                        TextInput::make('description')
                            ->columnSpanFull()
                            ->maxLength(500),
                    ])->columns(2),
            ]);
    }
}
