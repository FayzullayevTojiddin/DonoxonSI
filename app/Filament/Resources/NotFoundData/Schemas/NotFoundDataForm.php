<?php

namespace App\Filament\Resources\NotFoundData\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NotFoundDataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('intent')
                ->label('So‘rov (intent)')
                ->disabled()
                ->dehydrated(false),

            Toggle::make('status')
                ->label('Holati'),

            TextInput::make('details_from.ip')
                ->label('IP manzil')
                ->disabled()
                ->dehydrated(false),

            TextInput::make('details_from.user_agent')
                ->label('User Agent')
                ->disabled()
                ->dehydrated(false),

            TextInput::make('details_from.asked_at')
                ->label('So‘ralgan vaqt')
                ->disabled()
                ->dehydrated(false),
        ]);
    }
}
