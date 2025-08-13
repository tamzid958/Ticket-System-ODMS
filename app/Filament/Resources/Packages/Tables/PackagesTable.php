<?php

namespace App\Filament\Resources\Packages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->size(TextSize::Large)
                        ->weight(FontWeight::Bold)
                        ->color('primary')
                        ->formatStateUsing(fn ($state) => $state . ' Package')
                        ->searchable(),
                    TextColumn::make('monthly_quota')
                        ->formatStateUsing(fn ($state) => 'Quota: ' . $state),
                    TextColumn::make('base_price')
                        ->money('BDT')
                        ->formatStateUsing(fn ($state) => 'Price: ' . $state),
                ])
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([

            ]);
    }
}
