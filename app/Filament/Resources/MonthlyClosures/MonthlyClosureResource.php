<?php

namespace App\Filament\Resources\MonthlyClosures;

use App\Filament\Resources\MonthlyClosures\Pages\CreateMonthlyClosure;
use App\Filament\Resources\MonthlyClosures\Pages\EditMonthlyClosure;
use App\Filament\Resources\MonthlyClosures\Pages\ListMonthlyClosures;
use App\Filament\Resources\MonthlyClosures\Schemas\MonthlyClosureForm;
use App\Filament\Resources\MonthlyClosures\Tables\MonthlyClosuresTable;
use App\Models\MonthlyClosure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MonthlyClosureResource extends Resource
{
    protected static ?string $model = MonthlyClosure::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $recordTitleAttribute = 'php artisan make:filament-resource MonthlyClosure';

    public static function form(Schema $schema): Schema
    {
        return MonthlyClosureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonthlyClosuresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyClosures::route('/'),
            'create' => CreateMonthlyClosure::route('/create'),
            'edit' => EditMonthlyClosure::route('/{record}/edit'),
        ];
    }
}
