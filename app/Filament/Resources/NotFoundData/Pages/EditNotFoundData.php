<?php

namespace App\Filament\Resources\NotFoundData\Pages;

use App\Filament\Resources\NotFoundData\NotFoundDataResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotFoundData extends EditRecord
{
    protected static string $resource = NotFoundDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
