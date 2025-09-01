<?php

namespace App\Filament\Resources\ClientMonthlySummaries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClientMonthlySummaryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
             ->components([
                TextEntry::make('package_info')
                    ->label('Package Info')
                    ->getStateUsing(function ($record) {
                        return sprintf(
                            "%s | Quota: %s | Price: %s | Discount: %s",
                            $record->package?->name ?? '-',
                            $record->monthly_quota ?? 0,
                            number_format($record->package_price, 2),
                            $record->package_discount ? $record->package_discount : '0'
                        );
                    }),

                TextEntry::make('unit_used')->numeric(),
                TextEntry::make('excess_unit_used')->numeric(),
                TextEntry::make('excess_per_unit_price')->numeric(),
                TextEntry::make('excess_unit_cost')->numeric(),
                TextEntry::make('total_cost')->numeric(),
            ]);
    }
}
