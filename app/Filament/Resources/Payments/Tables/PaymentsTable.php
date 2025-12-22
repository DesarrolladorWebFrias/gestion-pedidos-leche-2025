<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use App\Models\Payment;
use Filament\Support\Colors\Color;

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
                    ->square()
                    ->url(fn ($record) => $record->receipt_url ? asset('storage/' . $record->receipt_url) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('balance_due')
                    ->label('Resta por Pagar')
                    ->money('MXN')
                    ->state(function (Payment $record): float {
                         // Calculate balance dynamically
                         $total = $record->order->total_amount;
                         // Sum of COMPLETED payments
                         $paid = $record->order->payments()
                             ->where('transaction_status', 'completado')
                             ->sum('payment_amount');
                         return max(0, $total - $paid);
                    })
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
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
                Action::make('validate')
                    ->label('Validar')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Validar Pago')
                    ->modalDescription('¿Confirmas que recibiste este pago? Esto actualizará el estado a completado.')
                    ->visible(fn (Payment $record) => $record->transaction_status === 'pendiente' && !auth()->user()->hasRole('client'))
                    ->action(function (Payment $record) {
                        $record->update(['transaction_status' => 'completado']);
                        
                        // Check if order is fully paid to update payment_status
                        $order = $record->order;
                        $totalPaid = $order->payments()->where('transaction_status', 'completado')->sum('payment_amount');
                        $newPending = max(0, $order->total_amount - $totalPaid);
                        
                        $status = 'pendiente';
                        if ($newPending <= 0) {
                            $status = 'liquidado';
                        } elseif ($newPending < $order->total_amount) {
                            $status = 'abonado';
                        }

                        $order->update([
                            'payment_status' => $status,
                            'pending_amount' => $newPending,
                        ]);

                        Notification::make()
                            ->title('Pago Validado')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Pago')
                    ->modalDescription('¿Estás seguro de invalidar este pago? Se marcará como fallido.')
                    ->visible(fn (Payment $record) => $record->transaction_status === 'pendiente' && !auth()->user()->hasRole('client'))
                    ->action(function (Payment $record) {
                        $record->update(['transaction_status' => 'fallido']);
                        
                        Notification::make()
                            ->title('Pago Rechazado')
                            ->danger()
                            ->send();
                    }),
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
