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
                                ->label('Cliente')
                                ->relationship('user', 'name')
                                ->default(auth()->id())
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->columnSpan(['default' => 2, 'md' => 1]),
                            Select::make('monthly_closure_id')
                                ->label('Cierre Mensual')
                                ->relationship('monthlyClosure', 'id', function ($query) {
                                    return $query->orderBy('year', 'desc')->orderBy('month', 'desc');
                                })
                                ->getOptionLabelFromRecordUsing(fn(MonthlyClosure $record) => "{$record->month}/{$record->year} - " . ucfirst($record->closure_status))
                                ->default(fn() => MonthlyClosure::where('closure_status', 'abierto')->latest('id')->first()?->id)
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->columnSpan(['default' => 2, 'md' => 1]),
                            DateTimePicker::make('order_date')
                                ->label('Fecha del Pedido')
                                ->default(now())
                                ->disabled()
                                ->dehydrated()
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
                                        ->options(function () {
                                            return Product::where('product_status', 'activo')
                                                ->where('inventory_status', 'disponible')
                                                ->get()
                                                ->mapWithKeys(function ($product) {
                                                    $url = $product->image_url ? asset('storage/' . $product->image_url) : null;
                                                    if (!$url) {
                                                        $name = urlencode($product->name);
                                                        $url = "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
                                                    }
                                                    $img = "<img src='{$url}' style='border-radius: 50%; width: 2rem; height: 2rem; object-fit: cover; display: inline-block; margin-right: 0.5rem;' class='w-8 h-8 rounded-full inline-block mr-2 object-cover'>";
                                                    
                                                    $pieces = (int) $product->pieces_per_box;
                                                    $piecesInfo = $pieces > 1 
                                                        ? " <span class='text-gray-500 text-xs'>(Caja de {$pieces} pzs)</span>" 
                                                        : "";

                                                    return [$product->id => "{$img}<span>{$product->name}</span>{$piecesInfo}"];
                                                });
                                        })
                                        ->allowHtml()
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $product = Product::find($state);
                                            
                                            // Auto-select type based on product preference
                                            $type = $product?->measurement_unit ?? 'pieza';
                                            $set('quantity_type', $type);
                                            
                                            self::updateTotals($get, $set);
                                        })
                                        ->columnSpan(['default' => 12, 'md' => 8]),
                                    Select::make('quantity_type')
                                        ->label('Tipo')
                                        ->options([
                                            'pieza' => 'Pieza',
                                            'caja' => 'Caja',
                                        ])
                                        ->default('pieza')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get) {
                                            self::updateTotals($get, $set);
                                        })
                                        ->columnSpan(['default' => 6, 'md' => 2]),
                                    TextInput::make('quantity')
                                        ->label('Cant.')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get) {
                                            self::updateTotals($get, $set);
                                        })
                                        ->columnSpan(['default' => 6, 'md' => 2]),
                                    TextInput::make('unit_price_at_order')
                                        ->label('P. Unit.')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->columnSpan(['default' => 12, 'md' => 6]),
                                    TextInput::make('subtotal')
                                        ->label('Subtotal')
                                        ->numeric()
                                        ->prefix('$')
                                        ->disabled()
                                        ->dehydrated()
                                        ->columnSpan(['default' => 12, 'md' => 6]),
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
                                ->disabledOn('create')
                                ->dehydrated()
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

    public static function updateTotals(Get $get, Set $set): void
    {
        $productId = $get('product_id');
        $quantityType = $get('quantity_type'); // 'pieza' or 'caja'
        $quantity = (float) $get('quantity');
        
        // Default to quantity 1 if invalid
        if ($quantity <= 0) $quantity = 1; 

        if (!$productId) {
             // If clearing product, subtract the old subtotal from total
             $oldSubtotal = (float) $get('subtotal');
             $currentTotal = (float) ($get('../../total_amount') ?? 0);
             $set('../../total_amount', max(0, $currentTotal - $oldSubtotal));

             $set('unit_price_at_order', 0);
             $set('subtotal', 0);
             return;
        }

        $product = Product::find($productId);
        if (!$product) return;

        // --- Logic based on database view `view_available_products` ---
        $dbPrice = (float) $product->current_unit_price;
        $dbUnit = $product->measurement_unit; // 'pieza' or 'caja'
        $piecesPerBox = (int) ($product->pieces_per_box ?? 1);
        if ($piecesPerBox < 1) $piecesPerBox = 1;

        // 1. Calculate the base price PER PIECE (Normalized)
        $pricePerPiece = $dbPrice;
        if ($dbUnit === 'caja') {
            $pricePerPiece = $dbPrice / $piecesPerBox;
        }

        // 2. Calculate the target price based on user selection
        // If user wants 'Caja', multiply by pieces. If 'Pieza', keep as is.
        $finalUnitPrice = $pricePerPiece;
        if ($quantityType === 'caja') {
            $finalUnitPrice = $pricePerPiece * $piecesPerBox;
        }

        // Round to 2 decimals
        $finalUnitPrice = round($finalUnitPrice, 2);
        $newSubtotal = round($finalUnitPrice * $quantity, 2);
        
        // 3. Update Grand Total using Delta (Difference)
        $oldSubtotal = (float) $get('subtotal');
        $currentTotal = (float) ($get('../../total_amount') ?? 0);
        
        // If this is the first calculation for a new row, oldSubtotal might be 0.
        // If modifying, oldSubtotal is the previous value.
        $diff = $newSubtotal - $oldSubtotal;
        
        $set('unit_price_at_order', $finalUnitPrice);
        $set('subtotal', $newSubtotal);
        $set('../../total_amount', round($currentTotal + $diff, 2));
    }
}
