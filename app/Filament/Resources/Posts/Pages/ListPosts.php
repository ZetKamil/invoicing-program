<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Actions\Blog\GenerateBlogPostWithAiAction;
use App\Enums\CategoryType;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateAi')
                ->label('Genereer met AI')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->modalHeading('Artikel genereren met Google Gemini AI')
                ->modalDescription('Voer een onderwerp of zoekterm in. Gemini stelt automatisch een titel, slug, samenvatting en gestructureerde secties (h2/h3) op.')
                ->modalSubmitActionLabel('Genereer artikel')
                ->form([
                    TextInput::make('topic')
                        ->label('Onderwerp of trefwoord')
                        ->required()
                        ->placeholder('bijv. Voeding en verzorging van Bengaalse kittens')
                        ->autofocus(),

                    Select::make('category_id')
                        ->label('Categorie')
                        ->options(fn () => Category::where('type', CategoryType::POST)->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Selecteer een categorie (optioneel)'),

                    Select::make('tone')
                        ->label('Toon van het artikel')
                        ->options([
                            'professioneel' => 'Professioneel & Informatief',
                            'toegankelijk'  => 'Toegankelijk & Enthousiast',
                            'expert'        => 'Diepgaande Expertgids',
                        ])
                        ->default('professioneel')
                        ->required(),
                ])
                ->action(function (array $data, GenerateBlogPostWithAiAction $generator) {
                    try {
                        $post = $generator->handle(
                            topic: $data['topic'],
                            categoryId: $data['category_id'] ?? null,
                            tone: $data['tone'] ?? 'professioneel',
                        );

                        Notification::make()
                            ->title('Artikel succesvol gegenereerd!')
                            ->body("Concept '{$post->title}' is aangemaakt met " . count($post->sections) . " secties.")
                            ->success()
                            ->send();

                        return redirect(PostResource::getUrl('edit', ['record' => $post]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Genereren met AI mislukt')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            CreateAction::make()->label('Nieuw Bericht'),
        ];
    }
}
