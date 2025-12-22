<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'monthlyClosure']))
            ->columns([
                TextColumn::make('id')
                    ->label('# Pedido')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('order_status')
                    ->label('Estado Pedido')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'confirmado' => 'info',
                        'en_camino' => 'warning',
                        'entregado' => 'success',
                        'cancelado' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'pendiente' => 'heroicon-m-clock',
                        'confirmado' => 'heroicon-m-check-circle',
                        'en_camino' => 'heroicon-m-truck',
                        'entregado' => 'heroicon-m-home',
                        'cancelado' => 'heroicon-m-x-circle',
                    }),
                TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'liquidado' => 'success',
                        'abonado' => 'warning',
                        'pendiente' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'liquidado' => 'heroicon-m-check-badge',
                        'abonado' => 'heroicon-m-currency-dollar',
                        'pendiente' => 'heroicon-m-exclamation-circle',
                    }),
                TextColumn::make('monthlyClosure.month')
                    ->label('Mes')
                    ->formatStateUsing(fn($state, $record) => "{$record->monthlyClosure->month}/{$record->monthlyClosure->year}"),
            ])
            ->filters([
                SelectFilter::make('order_status')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmado' => 'Confirmado',
                        'en_camino' => 'En Camino',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
                    ]),
                SelectFilter::make('payment_status')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'abonado' => 'Abonado',
                        'liquidado' => 'Liquidado',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order_date', 'desc');
    }
}
