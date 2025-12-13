<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;
use App\Enums\UserRole;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Statistika';

    protected static string|UnitEnum|null $navigationGroup = 'Tizim';

    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        $widgets = [
            \App\Filament\Widgets\StatsOverview::class,
        ];

        $user = auth()->user();

        if ($user && $user->role?->value === UserRole::SUPER_ADMIN->value) {
            $widgets[] = \App\Filament\Widgets\LatestRequests::class;
        }

        return $widgets;
    }
}