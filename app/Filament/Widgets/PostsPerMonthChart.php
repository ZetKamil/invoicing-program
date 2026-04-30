<?php
namespace App\Filament\Widgets;

use App\Models\Post; // nodig om posts op te halen
use Filament\Widgets\ChartWidget; // basisclass voor chart widgets
use Illuminate\Support\Carbon; // nodig om maanden op te bouwen

class PostsPerMonthChart extends ChartWidget
{
    protected ?string $heading = 'Posts per maand'; // titel boven de grafiek

    protected ?string $description = 'Overzicht van het aantal aangemaakte posts in de laatste 6 maanden.'; // extra uitleg

    // DODANA LINIJKA: Wykres na pełną szerokość
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function (int $monthsAgo) {
            return Carbon::now()->subMonths($monthsAgo); // vorige maanden opbouwen
        })->push(Carbon::now()); // huidige maand toevoegen

        $labels = $months->map(function (Carbon $month) {
            return $month->translatedFormat('M Y'); // bv. okt 2025
        })->toArray();

        $data = $months->map(function (Carbon $month) {
            return Post::query()
                ->whereYear('created_at', $month->year) // filter op jaar
                ->whereMonth('created_at', $month->month) // filter op maand
                ->count(); // tel aantal posts in die maand
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Aantal posts', // naam van dataset
                    'data' => $data, // waarden per maand
                ],
            ],
            'labels' => $labels, // x-as labels
        ];
    }

    protected function getType(): string
    {
        return 'line'; // lijngrafiek
    }
}
