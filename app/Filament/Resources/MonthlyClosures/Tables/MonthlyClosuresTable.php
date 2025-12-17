<?php

namespace App\Filament\Resources\MonthlyClosures\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class MonthlyClosuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('month')
                    ->label('Mes')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '1' => 'Enero', '2' => 'Febrero', '3' => 'Marzo', '4' => 'Abril',
                        '5' => 'Mayo', '6' => 'Junio', '7' => 'Julio', '8' => 'Agosto',
                        '9' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Año')
                    ->sortable(),
                TextColumn::make('closure_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'abierto' => 'success',
                        'cerrado' => 'warning',
                        'procesado' => 'gray',
                        default => 'gray',
                    }),
                IconColumn::make('in_inventory')
                    ->label('En Inventario')
                    ->boolean()
                    ->trueIcon('heroicon-o-archive-box')
                    ->falseIcon('heroicon-o-x-mark'),
                TextColumn::make('total_collected')
                    ->label('Recaudado')
                    ->money('MXN'),
                TextColumn::make('processed_orders')
                    ->label('Pedidos'),
                TextColumn::make('opening_date')
                    ->label('Apertura')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('closure_status')
                    ->options([
                        'abierto' => 'Abierto',
                        'cerrado' => 'Cerrado',
                        'procesado' => 'Procesado',
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
            ->defaultSort('year', 'desc');
    }
}
