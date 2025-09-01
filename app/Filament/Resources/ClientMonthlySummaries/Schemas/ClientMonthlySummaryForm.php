<?php

namespace App\Filament\Resources\ClientMonthlySummaries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientMonthlySummaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('client_id')
                    ->required()
                    ->numeric(),
                TextInput::make('year')
                    ->required()
                    ->numeric(),
                TextInput::make('month')
                    ->required()
                    ->numeric(),
                TextInput::make('monthly_quota')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('unit_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('excess_unit_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_cost')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('package_id')
                    ->numeric(),
                TextInput::make('excess_per_unit_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('package_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('package_discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('unit_discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('excess_unit_cost')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
