<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Filament\Events\Component\StateUpdated;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->description('Detalles básicos del usuario')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                        Textarea::make('address')
                            ->label('Dirección')
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ])->columns(2),

                Section::make('Configuración de Cuenta')
                    ->schema([
                        Select::make('user_type')
                            ->label('Tipo de Usuario')
                            ->options([
                                'admin' => 'Administrador',
                                'empleado' => 'Empleado',
                                'cliente' => 'Cliente',
                            ])
                            ->default('empleado')
                            ->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                            ])
                            ->default('activo')
                            ->required(),
                        DateTimePicker::make('registered_at')
                            ->label('Fecha de registro')
                            ->default(now())
                            ->required(),
                        Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columns(2),
            ]);
    }
}
