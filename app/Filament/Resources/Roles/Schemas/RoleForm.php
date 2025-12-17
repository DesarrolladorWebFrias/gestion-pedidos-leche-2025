<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del Rol')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('guard_name')
                    ->label('Guard')
                    ->default('web')
                    ->readOnly(),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Select::make('permissions')
                    ->label('Permisos Asignados')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }
}
