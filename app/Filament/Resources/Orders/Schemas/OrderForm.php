<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\MonthlyClosure;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
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
                Section::make('Información General del Pedido')
                    ->schema([
                        Select::make('user_id')
                            ->label('Cliente / Empleado')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->preload(),
                        Select::make('monthly_closure_id')
                            ->label('Cierre Mensual')
                            ->relationship('monthlyClosure', 'id', function ($query) {
                                return $query->orderBy('year', 'desc')->orderBy('month', 'desc');
                            })
                            ->getOptionLabelFromRecordUsing(fn(MonthlyClosure $record) => "{$record->month}/{$record->year} - " . ucfirst($record->closure_status))
                            ->default(fn() => MonthlyClosure::where('closure_status', 'abierto')->latest('id')->first()?->id)
                            ->required(),
                        DateTimePicker::make('order_date')
                            ->label('Fecha del Pedido')
                            ->default(now())
                            ->required(),
                        DatePicker::make('estimated_delivery_date')
                            ->label('Fecha Estimada de Entrega')
                            ->native(false),
                    ])->columns(2),

                Section::make('Detalles del Pedido')
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
                                    ->afterStateUpdated(fn($state, Set $set) => $set('unit_price_at_order', Product::find($state)?->current_unit_price ?? 0)),
                                Select::make('quantity_type')
                                    ->label('Tipo de Cantidad')
                                    ->options([
                                        'pieza' => 'Pieza',
                                        'caja' => 'Caja',
                                    ])
                                    ->default('pieza')
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Get $get, Set $set) => $set('subtotal', ($state * $get('unit_price_at_order')))),
                                TextInput::make('unit_price_at_order')
                                    ->label('Precio Unitario')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->readOnly()
                                    ->reactive(),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->readOnly(),
                            ])
                            ->columns(5)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = collect($get('details'))->sum(fn($detail) => $detail['subtotal'] ?? 0);
                                $set('total_amount', $total);
                            }),
                    ])->columnSpanFull(),

                Section::make('Totales y Estado')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Monto Total')
                            ->numeric()
                            ->prefix('$')
                            ->readOnly()
                            ->default(0),
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
                            ->required(),
                        Select::make('payment_method')
                            ->label('Método de Pago')
                            ->options([
                                'efectivo' => 'Efectivo',
                                'transferencia' => 'Transferencia',
                            ])
                            ->required(),
                        Select::make('payment_status')
                            ->label('Estado de Pago')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'abonado' => 'Abonado',
                                'liquidado' => 'Liquidado',
                            ])
                            ->default('pendiente')
                            ->required(),
                        Textarea::make('observations')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
