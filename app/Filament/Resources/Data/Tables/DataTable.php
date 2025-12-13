<?php

namespace App\Filament\Resources\Data\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
class DataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nomi')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('value')
                    ->label('Natija')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->value),

                IconColumn::make('status')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->label('Tahrirlash')->button(),
                    DeleteAction::make()->label('O\'chirish')->button(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}