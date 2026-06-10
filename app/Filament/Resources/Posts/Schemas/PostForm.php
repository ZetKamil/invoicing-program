<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('excerpt')
                            ->label('Excerpt')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Content')
                    ->schema([
                        RichEditor::make('body')
                            ->label('Body')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Publication')
                    ->schema([

                        ToggleButtons::make('status')
                            ->label('Status')
                            ->options(PostStatus::class)
                            ->default(PostStatus::DRAFT)
                            ->inline(),

                        DateTimePicker::make('published_at')
                            ->label('Published At'),
                    ])->columns(2),

                Section::make('Featured Image')
                    ->relationship('featuredImage')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Image')
                            ->image()
                            ->directory('posts')
                            ->columnSpanFull(),

                        TextInput::make('alt_text')
                            ->label('Alt Text'),

                        Hidden::make('is_featured')
                            ->default(true),

                        Hidden::make('disk')
                            ->default('public'),
                    ])->columns(2),
            ]);
    }
}
