<?php

namespace App\Filament\Resources\GlobalUserResource\Pages;

use App\Filament\Resources\GlobalUserResource;
use Filament\Resources\Pages\ListRecords;

class ListGlobalUsers extends ListRecords
{
    protected static string $resource = GlobalUserResource::class;
}
