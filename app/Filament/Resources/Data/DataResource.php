<?php

namespace App\Filament\Resources\Data;

use App\Filament\Resources\Data\Pages\CreateData;
use App\Filament\Resources\Data\Pages\EditData;
use App\Filament\Resources\Data\Pages\ListData;
use App\Filament\Resources\Data\Schemas\DataForm;
use App\Filament\Resources\Data\Tables\DataTable;
use App\Models\Data;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataResource extends Resource
{
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->role->value === 'super_admin';
    }
    
    protected static ?string $model = Data::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CodeBracketSquare;

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return DataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataTable::configure($table);
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
            'index' => ListData::route('/'),
        ];
    }
}
