<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles del Producto')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        TextInput::make('current_unit_price')
                            ->label('Precio Unitario Actual')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(0),
                    ])->columns(2),

                Section::make('Configuración de Venta')
                    ->schema([
                        Select::make('measurement_unit')
                            ->label('Unidad de Medida')
                            ->options([
                                'pieza' => 'Pieza',
                                'caja' => 'Caja',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('pieces_per_box')
                            ->label('Piezas por Caja')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->disabled(fn(Get $get) => $get('measurement_unit') !== 'caja')
                            ->dehydrated(),
                    ])->columns(2),

                Section::make('Estado e Inventario')
                    ->schema([
                        Select::make('inventory_status')
                            ->label('Estado de Inventario')
                            ->options([
                                'disponible' => 'Disponible',
                                'agotado' => 'Agotado',
                            ])
                            ->default('disponible')
                            ->required(),
                        Select::make('product_status')
                            ->label('Estado del Producto')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                            ])
                            ->default('activo')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
