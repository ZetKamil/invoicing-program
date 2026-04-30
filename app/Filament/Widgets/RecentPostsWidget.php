<?php
namespace App\Filament\Widgets;
use App\Models\Post; // nodig om recente posts op te halen
use Filament\Widgets\Widget; // basisclass voor custom widgets
class RecentPostsWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-posts-widget'; // niet static in jouw Filament-versie
protected int | string | array $columnSpan = 1; // widget over volledige breedte tonen
public function getRecentPosts()
{
    return Post::query()
        ->with('user') // auteur vooraf mee inladen
        ->latest() // nieuwste eerst
        ->take(5) // maximum 5 posts
        ->get();
}
}
