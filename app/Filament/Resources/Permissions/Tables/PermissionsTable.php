<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permiso')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group')
                    ->label('Grupo')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles_count')
                    ->label('Roles Asignados')
                    ->counts('roles')
                    ->badge(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options(fn() => \App\Models\Permission::distinct()->pluck('group', 'group')->toArray()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group', 'asc');
    }
}
