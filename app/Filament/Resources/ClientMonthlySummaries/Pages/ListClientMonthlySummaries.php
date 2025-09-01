<?php

namespace App\Filament\Resources\ClientMonthlySummaries\Pages;

use App\Filament\Resources\ClientMonthlySummaries\ClientMonthlySummaryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientMonthlySummaries extends ListRecords
{
    protected static string $resource = ClientMonthlySummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
//             CreateAction::make(),
        ];
    }
}
