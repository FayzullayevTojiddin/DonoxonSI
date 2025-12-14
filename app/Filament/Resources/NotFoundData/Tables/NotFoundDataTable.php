<?php

namespace App\Filament\Resources\NotFoundData\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;

class NotFoundDataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('intent')
                    ->label('So‘rov')
                    ->searchable()
                    ->limit(50),

                IconColumn::make('status')
                    ->label('Holati')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Yaratilgan vaqt')
                    ->dateTime('d.m.Y H:i'),

                TextColumn::make('updated_at')
                    ->label('Hal qilingan vaqt')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label("Ko'rish"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
