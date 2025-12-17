<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\MonthlyClosure;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Imformación General')
                        ->description('Datos del cliente y fechas')
                        ->icon('heroicon-m-user')
                        ->schema([
                            Select::make('user_id')
                                ->label('Cliente / Empleado')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->columnSpan(['default' => 2, 'md' => 1]),
                            Select::make('monthly_closure_id')
                                ->label('Cierre Mensual')
                                ->relationship('monthlyClosure', 'id', function ($query) {
                                    return $query->orderBy('year', 'desc')->orderBy('month', 'desc');
                                })
                                ->getOptionLabelFromRecordUsing(fn(MonthlyClosure $record) => "{$record->month}/{$record->year} - " . ucfirst($record->closure_status))
                                ->default(fn() => MonthlyClosure::where('closure_status', 'abierto')->latest('id')->first()?->id)
                                ->required()
                                ->columnSpan(['default' => 2, 'md' => 1]),
                            DateTimePicker::make('order_date')
                                ->label('Fecha del Pedido')
                                ->default(now())
                                ->required()
                                ->columnSpan(['default' => 2, 'md' => 1]),
                            DatePicker::make('estimated_delivery_date')
                                ->label('Fecha Estimada de Entrega')
                                ->native(false)
                                ->columnSpan(['default' => 2, 'md' => 1]),
                        ])
                        ->columns(['default' => 1, 'md' => 2]),

                    Step::make('Productos')
                        ->description('Selección de artículos')
                        ->icon('heroicon-m-shopping-cart')
                        ->schema([
                            Repeater::make('details')
                                ->relationship()
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Producto')
                                        ->options(Product::where('product_status', 'activo')->where('inventory_status', 'disponible')->pluck('name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, Set $set) => $set('unit_price_at_order', Product::find($state)?->current_unit_price ?? 0))
                                        ->columnSpan(['default' => 12, 'md' => 4]),
                                    Select::make('quantity_type')
                                        ->label('Tipo')
                                        ->options([
                                            'pieza' => 'Pieza',
                                            'caja' => 'Caja',
                                        ])
                                        ->default('pieza')
                                        ->required()
                                        ->columnSpan(['default' => 12, 'md' => 2]),
                                    TextInput::make('quantity')
                                        ->label('Cant.')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, Get $get, Set $set) => $set('subtotal', ($state * $get('unit_price_at_order'))))
                                        ->columnSpan(['default' => 12, 'md' => 2]),
                                    TextInput::make('unit_price_at_order')
                                        ->label('P. Unit.')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->readOnly()
                                        ->reactive()
                                        ->columnSpan(['default' => 12, 'md' => 2]),
                                    TextInput::make('subtotal')
                                        ->label('Subtotal')
                                        ->numeric()
                                        ->prefix('$')
                                        ->readOnly()
                                        ->columnSpan(['default' => 12, 'md' => 2]),
                                ])
                                ->columns(12)
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $total = collect($get('details'))->sum(fn($detail) => $detail['subtotal'] ?? 0);
                                    $set('total_amount', $total);
                                }),
                        ]),

                    Step::make('Totales y Estado')
                        ->description('Resumen y finalización')
                        ->icon('heroicon-m-check-badge')
                        ->schema([
                            TextInput::make('total_amount')
                                ->label('Monto Total')
                                ->numeric()
                                ->prefix('$')
                                ->readOnly()
                                ->default(0)
                                ->extraInputAttributes(['style' => 'font-size: 1.5rem; font-weight: bold; text-align: right;'])
                                ->columnSpanFull(),
                            Select::make('order_status')
                                ->label('Estado del Pedido')
                                ->options([
                                    'pendiente' => 'Pendiente',
                                    'confirmado' => 'Confirmado',
                                    'en_camino' => 'En Camino',
                                    'entregado' => 'Entregado',
                                    'cancelado' => 'Cancelado',
                                ])
                                ->default('pendiente')
                                ->required()
                                ->native(false)
                                ->columnSpan(['default' => 1, 'md' => 1]),
                            Select::make('payment_method')
                                ->label('Método de Pago')
                                ->options([
                                    'efectivo' => 'Efectivo',
                                    'transferencia' => 'Transferencia',
                                ])
                                ->required()
                                ->native(false)
                                ->columnSpan(['default' => 1, 'md' => 1]),
                            Select::make('payment_status')
                                ->label('Estado de Pago')
                                ->options([
                                    'pendiente' => 'Pendiente',
                                    'abonado' => 'Abonado',
                                    'liquidado' => 'Liquidado',
                                ])
                                ->default('pendiente')
                                ->required()
                                ->native(false)
                                ->columnSpan(['default' => 1, 'md' => 1]),
                            Textarea::make('observations')
                                ->label('Observaciones')
                                ->columnSpanFull(),
                        ])
                        ->columns(['default' => 1, 'md' => 3]),
                ])
                ->columnSpanFull()
            ]);
    }
}
