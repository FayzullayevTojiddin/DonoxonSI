<?php

namespace App\Filament\Resources\Data\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class DataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nomi')
                ->required()
                ->maxLength(255),

            TextInput::make('key')
                ->label('Kalit so\'z')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Textarea::make('value')
                ->label('Natija')
                ->required()
                ->columnSpanFull()
                ->maxLength(65535),

            Toggle::make('status')
                ->label('Status')
                ->default(true)
                ->inline(false),
        ]);
    }
}
