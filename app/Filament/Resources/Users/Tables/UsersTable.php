<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('user_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'empleado' => 'warning',
                        'cliente' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge(),
                TextColumn::make('registered_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
