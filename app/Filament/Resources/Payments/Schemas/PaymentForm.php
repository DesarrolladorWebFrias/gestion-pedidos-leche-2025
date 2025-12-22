<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Pago')
                    ->schema([
                        Select::make('order_id')
                            ->label('Pedido')
                            ->relationship(
                                'order', 
                                'id', 
                                modifyQueryUsing: fn (Builder $query) => $query->where('payment_status', '!=', 'liquidado')->orderBy('created_at', 'desc')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "Pedido #{$record->id} - {$record->user->name} ($ {$record->total_amount}) - " . ucfirst($record->payment_status))
                            ->default(fn () => Order::where('payment_status', '!=', 'liquidado')->latest()->first()?->id)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $set('order_id', $state))
                            ->required(),
                        \Filament\Forms\Components\Placeholder::make('payment_stats')
                            ->label('Estado de Cuenta del Pedido')
                            ->content(function ($get) {
                                $orderId = $get('order_id');
                                if (! $orderId) {
                                    return 'Seleccione un pedido para ver el saldo.';
                                }
                                $order = Order::find($orderId);
                                if (! $order) {
                                    return 'Pedido no encontrado.';
                                }

                                $totalPaid = $order->payments()->where('transaction_status', 'completado')->sum('payment_amount');
                                $remaining = $order->total_amount - $totalPaid;
                                
                                $color = $remaining > 0 ? 'text-danger-600' : 'text-success-600';
                                
                                return new \Illuminate\Support\HtmlString("
                                    <div class='grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg'>
                                        <div>
                                            <span class='text-sm text-gray-500'>Total del Pedido</span>
                                            <div class='text-lg font-bold'>$ " . number_format($order->total_amount, 2) . "</div>
                                        </div>
                                        <div>
                                            <span class='text-sm text-gray-500'>Total Abonado</span>
                                            <div class='text-lg font-bold text-success-600'>$ " . number_format($totalPaid, 2) . "</div>
                                        </div>
                                        <div>
                                            <span class='text-sm text-gray-500'>Saldo Pendiente</span>
                                            <div class='text-lg font-bold $color'>$ " . number_format($remaining, 2) . "</div>
                                        </div>
                                    </div>
                                ");
                            })
                            ->columnSpanFull(),
                        DateTimePicker::make('payment_date')
                            ->label('Fecha de Pago')
                            ->default(now())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('payment_amount')
                            ->label('Monto Pagado')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Select::make('payment_method_used')
                            ->label('Método de Pago')
                            ->options([
                                'efectivo' => 'Efectivo',
                                'transferencia' => 'Transferencia',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('transfer_reference')
                            ->label('Referencia')
                            ->visible(fn($get) => $get('payment_method_used') === 'transferencia')
                            ->required(fn($get) => $get('payment_method_used') === 'transferencia'),
                        Select::make('transaction_status')
                            ->label('Estado de Transacción')
                            ->options([
                                'completado' => 'Completado',
                                'pendiente' => 'Pendiente',
                                'fallido' => 'Fallido',
                            ])
                            ->default('completado')
                            ->required(),
                        FileUpload::make('receipt_url')
                            ->label('Comprobante')
                            ->directory('receipts')
                            ->columnSpanFull(),
                    ])->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
