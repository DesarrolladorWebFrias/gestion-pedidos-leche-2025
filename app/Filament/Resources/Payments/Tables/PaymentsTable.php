<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')
                    ->label('# Pedido')
                    ->sortable(),
                TextColumn::make('order.user.name')
                    ->label('Cliente'),
                TextColumn::make('payment_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('payment_amount')
                    ->label('Monto')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('payment_method_used')
                    ->label('Método')
                    ->badge()
                    ->color('info'),
                TextColumn::make('transaction_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completado' => 'success',
                        'pendiente' => 'warning',
                        'fallido' => 'danger',
                        default => 'gray',
                    }),
                ImageColumn::make('receipt_url')
                    ->label('Comprobante')
                    ->square(),
            ])
            ->filters([
                SelectFilter::make('payment_method_used')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                    ]),
                SelectFilter::make('transaction_status')
                    ->options([
                        'completado' => 'Completado',
                        'pendiente' => 'Pendiente',
                        'fallido' => 'Fallido',
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
            ->defaultSort('payment_date', 'desc');
    }
}
