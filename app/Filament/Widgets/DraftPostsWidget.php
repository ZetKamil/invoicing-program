<?php
namespace App\Filament\Widgets;
use App\Models\Post; // nodig om drafts op te halen
use Filament\Widgets\Widget; // basisclass voor custom widgets
class DraftPostsWidget extends Widget
{
    protected string $view = 'filament.widgets.draft-posts-widget'; // niet static in jouw Filament-versie
protected int | string | array $columnSpan = 1; // widget over volledige breedte tonen
public function getDraftPosts()
{
    return Post::query()
        ->with('user') // auteur mee ophalen
        ->where('is_published', false) // enkel niet-gepubliceerde posts
        ->latest() // nieuwste eerst
        ->take(5) // maximum 5 drafts
        ->get();
}
}
