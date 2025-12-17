<?php

namespace App\Filament\Resources\MonthlyClosures\Pages;

use App\Filament\Resources\MonthlyClosures\MonthlyClosureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonthlyClosures extends ListRecords
{
    protected static string $resource = MonthlyClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
