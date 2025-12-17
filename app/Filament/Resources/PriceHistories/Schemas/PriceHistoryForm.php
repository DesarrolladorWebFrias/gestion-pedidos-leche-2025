<?php

namespace App\Filament\Resources\PriceHistories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PriceHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->disabled()
                    ->required(),
                TextInput::make('previous_price')
                    ->label('Precio Anterior')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),
                TextInput::make('new_price')
                    ->label('Nuevo Precio')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),
                DateTimePicker::make('change_date')
                    ->label('Fecha de Cambio')
                    ->disabled(),
                Select::make('changed_by_user_id')
                    ->label('Modificado Por')
                    ->relationship('changedBy', 'name')
                    ->disabled(),
            ]);
    }
}
