<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\TransactionEntry;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Company;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Transactions', TransactionEntry::count())
                ->description('Total entries')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),
            Stat::make('Products', Product::count())
                ->description('Items in catalog')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),
            Stat::make('Customers', Customer::count())
                ->description('Active customers')
                ->descriptionIcon('heroicon-o-users')
                ->color('warning'),
            Stat::make('Companies', Company::count())
                ->description('Registered companies')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),
        ];
    }
}
