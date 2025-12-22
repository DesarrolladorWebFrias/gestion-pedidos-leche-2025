<?php

namespace App\Filament\Resources\PriceHistories;

use App\Filament\Resources\PriceHistories\Pages\ListPriceHistories;
use App\Filament\Resources\PriceHistories\Schemas\PriceHistoryForm;
use App\Filament\Resources\PriceHistories\Tables\PriceHistoriesTable;
use App\Models\PriceHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceHistoryResource extends Resource
{
    protected static ?string $model = PriceHistory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Historial de Precios';

    protected static ?string $modelLabel = 'Historial de Precio';

    protected static ?string $pluralModelLabel = 'Historial de Precios';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected static ?string $recordTitleAttribute = 'product.name';

    public static function form(Schema $schema): Schema
    {
        return PriceHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceHistoriesTable::configure($table);
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
            'index' => ListPriceHistories::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
