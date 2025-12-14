<?php

namespace App\Filament\Resources\NotFoundData\Pages;

use App\Filament\Resources\NotFoundData\NotFoundDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotFoundData extends ListRecords
{
    protected static string $resource = NotFoundDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
