<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ClientRecentOrders extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'My Recent Orders';

    public static function canView(): bool
    {
        // Visible only to clients
        return Auth::check() && Auth::user()->hasRole('client');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Ensure they only see their own orders
                Order::query()->where('user_id', Auth::id())->latest('created_at')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('mxn'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date(),
            ]); // No actions needed for simple view, or link to full view if possible
    }
}
