<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label('Categorie')
                    ->badge()
                    ->color('success')
                    ->placeholder('Geen categorie')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        PostStatus::PUBLISHED->value, 'published' => 'success',
                        default                                   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state?->value ?? $state) {
                        PostStatus::PUBLISHED->value, 'published' => 'Gepubliceerd',
                        PostStatus::DRAFT->value, 'draft'         => 'Concept',
                        default                                   => $state,
                    })
                    ->sortable(),

                TextColumn::make('sections_count')
                    ->label('Secties')
                    ->getStateUsing(fn (Post $record) => is_array($record->sections) ? count($record->sections) . ' secties' : '0 secties')
                    ->badge()
                    ->color('info'),

                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Publicatiedatum')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Aangemaakt op')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        PostStatus::DRAFT->value     => 'Concept',
                        PostStatus::PUBLISHED->value => 'Gepubliceerd',
                    ]),

                SelectFilter::make('category_id')
                    ->label('Categorie')
                    ->relationship('category', 'name', fn ($query) => $query->where('type', 'post')),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
