<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Orders per Month';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Visible only to internal staff
        return Auth::check() && Auth::user()->hasAnyRole(['super_admin', 'admin', 'manager', 'employee']);
    }

    protected function getData(): array
    {
        // Manual grouping to avoid dependency on laravel-trend if not present
        // Get orders for last 12 months
        $data = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $data->pluck('month')->map(function ($month) {
            return Carbon::createFromFormat('Y-m', $month)->format('M Y');
        })->toArray();
        
        $counts = $data->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $counts,
                    'fill' => true,
                    'borderColor' => '#F59E0B', // Amber
                    'pointBackgroundColor' => '#F59E0B',
                    'pointBorderColor' => '#fff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
