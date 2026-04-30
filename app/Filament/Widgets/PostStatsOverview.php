<?php
namespace App\Filament\Widgets;
use App\Models\Category; // nodig om aantal categorieën op te halen
use App\Models\Post; // nodig om poststatistieken op te halen
use Filament\Widgets\StatsOverviewWidget; // basisclass voor stats widgets
use Filament\Widgets\StatsOverviewWidget\Stat; // individuele stat card
class PostStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalPosts = Post::query()->count(); // totaal aantal posts
        $publishedPosts = Post::query()
            ->where('is_published', true) // enkel gepubliceerde posts
            ->count();
        $draftPosts = Post::query()
            ->where('is_published', false) // enkel drafts
->count();
$totalCategories = Category::query()->count(); // totaal aantal categorieën
return [
    Stat::make('Totaal posts', $totalPosts) // eerste kaart
    ->description('Alle posts in het systeem') // uitleg onder de waarde
    ->descriptionIcon('heroicon-o-document-text') // icoon naast de beschrijving
        ->color('primary'), // primaire kleur
Stat::make('Gepubliceerd', $publishedPosts) // tweede kaart
->description('Posts die live staan') // extra uitleg
->descriptionIcon('heroicon-o-check-circle') // visueel icoon
->color('success'), // groene kleur
Stat::make('Drafts', $draftPosts) // derde kaart
->description('Posts die nog niet live staan') // extra uitleg
    ->descriptionIcon('heroicon-o-pencil-square') // draft-icoon
    ->color('warning'), // opvallende kleur
Stat::make('Categorieën', $totalCategories) // vierde kaart
->description('Beschikbare categorieën') // extra uitleg
->descriptionIcon('heroicon-o-rectangle-stack') // icoon voor categorieën
    ->color('info'), // info-kleur
];
}
}
