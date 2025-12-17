<?php

namespace App\Filament\Resources\PriceHistories\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('previous_price')
                    ->label('Precio Anterior')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('new_price')
                    ->label('Nuevo Precio')
                    ->money('MXN')
                    ->sortable()
                    ->color(fn($record) => $record->new_price > $record->previous_price ? 'danger' : 'success')
                    ->description(fn($record) => $record->new_price > $record->previous_price ? 'Aumento' : 'Disminución'),
                TextColumn::make('change_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('changedBy.name')
                    ->label('Modificado Por')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // Read-only
            ])
            ->defaultSort('change_date', 'desc');
    }
}
