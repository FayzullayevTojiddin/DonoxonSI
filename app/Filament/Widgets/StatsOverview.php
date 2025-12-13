<?php

namespace App\Filament\Widgets;

use App\Models\Request;
use App\Models\Data;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $uniqueVisitors = Request::query()
            ->whereNotNull('details_from')
            ->get()
            ->pluck('details_from')
            ->pluck('ip')
            ->unique()
            ->count();

        $dataCount = Data::count();

        $requestsCount = Request::count();

        $unreadRequests = Request::where('readed', false)->count();

        return [
            Stat::make("Tashrif buyurganlar", $uniqueVisitors)
                ->description('Unique foydalanuvchilar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make("Ma'lumotlar", $dataCount)
                ->description('Jami ma\'lumotlar soni')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make("So'rovlar", $requestsCount)
                ->description($unreadRequests > 0 ? "{$unreadRequests} ta yangi" : "Yangi so'rov yo'q")
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($unreadRequests > 0 ? 'warning' : 'success'),
        ];
    }
}