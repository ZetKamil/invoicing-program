<?php

namespace App\Filament\Resources\WebsitePages\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Paginadetails')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel van de pagina')
                            ->placeholder('bijv. Home, Over Ons, Diensten, Contact')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set, callable $get) {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->helperText('Pad in de adresbalk, bijv. "home" of "over-ons"'),

                        Toggle::make('is_homepage')
                            ->label('Instellen als Hoofdpagina (Homepage)')
                            ->helperText('Deze pagina wordt geladen als startpagina van uw website.'),

                        Toggle::make('is_published')
                            ->label('Gepubliceerd')
                            ->default(true)
                            ->helperText('Zichtbaar voor bezoekers op de openbare website.'),
                    ])->columns(2),

                Section::make('Visuele Blokken van de Pagina (Klocken)')
                    ->schema([
                        Repeater::make('blocks')
                            ->label('Klocken')
                            ->itemLabel(fn (array $state): ?string => match ($state['type'] ?? '') {
                                'hero' => '🌟 Hero Banner (' . ($state['data']['title'] ?? 'Geen titel') . ')',
                                'features' => '⚡ Kenmerken & Voordelen',
                                'catalog_grid' => '📦 Producten & Diensten Grid',
                                'testimonials' => '💬 Klantbeoordelingen',
                                'faq' => '❓ Veelgestelde Vragen',
                                'contact_form' => '✉️ Contactformulier',
                                default => 'Nieuw Blok (' . ($state['type'] ?? 'Onbekend') . ')',
                            })
                            ->schema([
                                TextInput::make('id')
                                    ->default(fn () => (string) Str::ulid())
                                    ->required()
                                    ->readOnly()
                                    ->columnSpan(1),

                                Select::make('type')
                                    ->label('Type Blok')
                                    ->options([
                                        'hero' => 'Hero Banner (Koptekst + Knop + Achtergrond)',
                                        'features' => 'Kenmerken & Voordelen (Iconen + Tekst)',
                                        'catalog_grid' => 'Aanbod Grid (Producten & Diensten)',
                                        'testimonials' => 'Klantbeoordelingen & Reviews',
                                        'faq' => 'Veelgestelde Vragen (FAQ)',
                                        'contact_form' => 'Contactformulier & Offerteaanvraag',
                                    ])
                                    ->required()
                                    ->columnSpan(1),

                                KeyValue::make('data')
                                    ->label('Blokgegevens & Teksten')
                                    ->keyLabel('Instelling / Veld')
                                    ->valueLabel('Tekst / Waarde')
                                    ->reorderable()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->columnSpanFull()
                            ->helperText('Bouw de pagina modulair op met klocken. Deze kunnen ook autonoom beheerd worden door uw AI-agent.'),
                    ]),

                Section::make('SEO & Zoekmachine Optimalisatie')
                    ->schema([
                        KeyValue::make('seo_meta')
                            ->label('Meta Tags')
                            ->keyLabel('Tag (bijv. meta_title, meta_description, og_image)')
                            ->valueLabel('Inhoud')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
