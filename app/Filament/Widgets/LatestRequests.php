<?php

namespace App\Filament\Widgets;

use App\Models\Request;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\Requests\Tables\RequestsTable;

class LatestRequests extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return RequestsTable::configure($table)
            ->query(
                Request::query()
                    ->latest()
                    ->limit(10)
            );
    }
}