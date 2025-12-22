<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->measurement_unit === 'caja'
                        ? "Caja de {$record->pieces_per_box} pzas"
                        : 'Venta por pieza'),
                TextColumn::make('current_unit_price')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('inventory_status')
                    ->label('Inventario')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'disponible' => 'success',
                        'agotado' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('product_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'activo' => 'info',
                        'inactivo' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('updated_act')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('inventory_status')
                    ->options([
                        'disponible' => 'Disponible',
                        'agotado' => 'Agotado',
                    ]),
                SelectFilter::make('product_status')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ]),
            ])
            ->recordActions([
                // History action removed
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
