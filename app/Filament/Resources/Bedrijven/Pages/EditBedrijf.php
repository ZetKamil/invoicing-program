<?php

namespace App\Filament\Resources\Bedrijven\Pages;

use App\Filament\Resources\Bedrijven\BedrijfResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBedrijf extends EditRecord
{
    protected static string $resource = BedrijfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
