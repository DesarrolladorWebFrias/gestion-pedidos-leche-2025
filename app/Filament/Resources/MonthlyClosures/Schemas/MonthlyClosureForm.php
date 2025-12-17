<?php

namespace App\Filament\Resources\MonthlyClosures\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MonthlyClosureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Período')
                    ->description('Definición del mes y año del cierre')
                    ->schema([
                        Select::make('month')
                            ->label('Mes')
                            ->options([
                                1 => 'Enero',
                                2 => 'Febrero',
                                3 => 'Marzo',
                                4 => 'Abril',
                                5 => 'Mayo',
                                6 => 'Junio',
                                7 => 'Julio',
                                8 => 'Agosto',
                                9 => 'Septiembre',
                                10 => 'Octubre',
                                11 => 'Noviembre',
                                12 => 'Diciembre'
                            ])
                            ->required(),
                        TextInput::make('year')
                            ->label('Año')
                            ->required()
                            ->default(now()->year)
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100),
                    ])->columns(2),

                Section::make('Estado del Cierre')
                    ->schema([
                        Select::make('closure_status')
                            ->label('Estado')
                            ->options([
                                'abierto' => 'Abierto',
                                'cerrado' => 'Cerrado',
                                'procesado' => 'Procesado Contablemente',
                            ])
                            ->default('abierto')
                            ->required(),
                        Toggle::make('in_inventory')
                            ->label('¿En Inventario?')
                            ->onIcon('heroicon-m-archive-box')
                            ->offIcon('heroicon-m-x-mark')
                            ->onColor('warning')
                            ->helperText('Activar si se está realizando inventario físico. Bloqueará nuevos pedidos.'),
                    ])->columns(2),

                Section::make('Fechas Clave')
                    ->schema([
                        DateTimePicker::make('opening_date')
                            ->label('Fecha Apertura')
                            ->default(now())
                            ->required(),
                        DateTimePicker::make('closing_date')
                            ->label('Fecha Cierre'),
                        DatePicker::make('inventory_date')
                            ->label('Fecha Inventario'),
                    ])->columns(3),

                Section::make('Resumen (Automático)')
                    ->collapsed()
                    ->schema([
                        TextInput::make('total_collected')
                            ->label('Recaudado')
                            ->numeric()
                            ->prefix('$')
                            ->readOnly(),
                        TextInput::make('processed_orders')
                            ->label('Pedidos Procesados')
                            ->numeric()
                            ->readOnly(),
                    ])->columns(2),
            ]);
    }
}
