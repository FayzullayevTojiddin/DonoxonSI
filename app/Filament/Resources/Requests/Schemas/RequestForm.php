<?php

namespace App\Filament\Resources\Requests\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;

class RequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('full_name')
                ->label("F.I.Sh")
                ->disabled()
                ->dehydrated(false),

            Textarea::make('request')
                ->label("So'rov")
                ->disabled()
                ->dehydrated(false)
                ->rows(4),

            Toggle::make('readed')
                ->label("O'qilgan")
                ->default(false),

            Section::make("Yuborilgan ma'lumotlar")
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('details_from.ip')
                            ->label("IP manzil")
                            ->content(fn ($record) => $record?->details_from['ip'] ?? '-'),

                        Placeholder::make('details_from.submitted_at')
                            ->label("Yuborilgan vaqt")
                            ->content(fn ($record) => $record?->details_from['submitted_at'] ?? '-'),
                    ]),

                    Textarea::make('user_agent_display')
                        ->label("Brauzer ma'lumoti")
                        ->default(fn ($record) => $record?->details_from['user_agent'] ?? '-')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(false),

        ]);
    }
}