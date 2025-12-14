<?php

namespace App\Filament\Resources\NotFoundData;

use App\Filament\Resources\NotFoundData\Pages\CreateNotFoundData;
use App\Filament\Resources\NotFoundData\Pages\EditNotFoundData;
use App\Filament\Resources\NotFoundData\Pages\ListNotFoundData;
use App\Filament\Resources\NotFoundData\Schemas\NotFoundDataForm;
use App\Filament\Resources\NotFoundData\Tables\NotFoundDataTable;
use App\Models\NotFoundData;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotFoundDataResource extends Resource
{
    protected static ?string $model = NotFoundData::class;

    protected static ?string $navigationLabel = "Javob berilmagan so'rovlar";

    protected static ?string $modelLabel = "Javob berilmagan so'rov";

    protected static ?string $pluralModelLabel = "Javob berilmagan so'rovlar";

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', false)->count();
    }
   
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return NotFoundDataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotFoundDataTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotFoundData::route('/'),
        ];
    }
}
