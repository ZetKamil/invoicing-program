<?php

namespace App\Filament\Resources\Bedrijven\Pages;

use App\Filament\Resources\Bedrijven\BedrijfResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBedrijven extends ListRecords
{
    protected static string $resource = BedrijfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
