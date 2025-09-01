<?php

namespace App\Filament\Resources\ClientMonthlySummaries\Pages;

use App\Filament\Resources\ClientMonthlySummaries\ClientMonthlySummaryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewClientMonthlySummary extends ViewRecord
{
    protected static string $resource = ClientMonthlySummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
