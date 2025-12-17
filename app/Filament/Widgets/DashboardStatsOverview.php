<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Safety check if user is not logged in
        if (!$user) {
            return [];
        }

        // Administrator / Super Admin Stats
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return [
                Stat::make('Total Orders', Order::count())
                    ->description('All time orders')
                    ->descriptionIcon('heroicon-m-shopping-bag')
                    ->color('primary'),
                    
                Stat::make('Total Revenue', '$' . number_format(Order::sum('total_amount'), 2))
                    ->description('Total sales amount')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('success'),
                    
                Stat::make('New Users', User::where('created_at', '>=', now()->subDays(30))->count())
                    ->description('Last 30 days')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('info'),
            ];
        }

        // Employee / Manager Stats
        if ($user->hasAnyRole(['employee', 'manager'])) {
            return [
                Stat::make('Orders Today', Order::where('created_at', '>=', now()->startOfDay())->count())
                    ->description('New orders received today')
                    ->descriptionIcon('heroicon-m-calendar-days'),
                    
                Stat::make('Pending Orders', Order::where('order_status', 'pending')->count()) 
                    ->description('Orders needing attention')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),
                    
                Stat::make('Processing Orders', Order::where('order_status', 'processing')->count()) 
                    ->description('Orders in progress')
                    ->descriptionIcon('heroicon-m-arrow-path')
                    ->color('info'),
            ];
        }

        // Client Stats
        if ($user->hasRole('client')) {
            return [
                Stat::make('My Orders', Order::where('user_id', $user->id)->count())
                    ->description('Total orders placed')
                    ->descriptionIcon('heroicon-m-shopping-cart')
                    ->color('primary'),
                    
                Stat::make('Total Spent', '$' . number_format(Order::where('user_id', $user->id)->sum('total_amount'), 2))
                    ->description('Lifetime spend')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),
            ];
        }

        return [];
    }
}
