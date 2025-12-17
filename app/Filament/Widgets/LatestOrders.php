<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    
    // Heading for the widget
    protected static ?string $heading = 'Latest Orders';

    public static function canView(): bool
    {
        // Visible only to internal staff
        return Auth::check() && Auth::user()->hasAnyRole(['super_admin', 'admin', 'manager', 'employee']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest('created_at')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->color('primary')
                    ->weight('bold')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record)),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
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
                    ->money('mxn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
