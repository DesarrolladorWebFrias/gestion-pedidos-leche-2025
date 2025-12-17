<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del Permiso')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('guard_name')
                    ->label('Guard')
                    ->default('web')
                    ->readOnly(),
                TextInput::make('group')
                    ->label('Grupo')
                    ->maxLength(100),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
            ]);
    }
}
