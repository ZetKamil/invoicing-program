<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
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
                Section::make('Basisinformatie')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->placeholder('bijv. De complete gids voor de verzorging van kittens')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->helperText('Unieke identificatie in de adresbalk van de browser'),

                        Select::make('category_id')
                            ->label('Categorie (Blog)')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('type', 'post')
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecteer een blogcategorie (optioneel)')
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->label('Korte samenvatting (Excerpt)')
                            ->placeholder('Korte inleiding voor de overzichtspagina en meta description...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Secties van het artikel (Gestructureerde Blog / AI-Ready)')
                    ->schema([
                        Repeater::make('sections')
                            ->label('Artikel secties')
                            ->itemLabel(fn (array $state): ?string => $state['heading'] ?? 'Nieuwe sectie')
                            ->schema([
                                TextInput::make('heading')
                                    ->label('Koptekst van de sectie')
                                    ->placeholder('bijv. Waarom hoogwaardige voeding essentieel is')
                                    ->required(),

                                Select::make('level')
                                    ->label('Niveau koptekst')
                                    ->options([
                                        'h2' => 'H2 - Hoofdsectie',
                                        'h3' => 'H3 - Subsectie',
                                    ])
                                    ->default('h2')
                                    ->required(),

                                Textarea::make('content')
                                    ->label('Inhoud van de sectie')
                                    ->rows(4)
                                    ->placeholder('Paragrafen, tips en toelichting...')
                                    ->columnSpanFull(),

                                TextInput::make('image_url')
                                    ->label('Afbeelding URL / Pad')
                                    ->placeholder('https://... of /storage/posts/...'),

                                TextInput::make('image_caption')
                                    ->label('Bijschrift afbeelding')
                                    ->placeholder('Korte toelichting onder de afbeelding'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->helperText('Modulair opgebouwde secties maken het artikel flexibel renderbaar en direct compatibel met AI Structured Outputs.'),
                    ]),

                Section::make('Publicatie')
                    ->schema([
                        ToggleButtons::make('status')
                            ->label('Publicatiestatus')
                            ->options(PostStatus::class)
                            ->default(PostStatus::DRAFT)
                            ->inline(),

                        DateTimePicker::make('published_at')
                            ->label('Publicatiedatum'),
                    ])->columns(2),

                Section::make('Uitgelichte afbeelding')
                    ->relationship('featuredImage')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Afbeelding')
                            ->image()
                            ->directory('posts')
                            ->columnSpanFull(),

                        TextInput::make('alt_text')
                            ->label('Alt tekst (voor SEO)'),

                        Hidden::make('is_featured')
                            ->default(true),

                        Hidden::make('disk')
                            ->default('public'),
                    ])->columns(2),
            ]);
    }
}
