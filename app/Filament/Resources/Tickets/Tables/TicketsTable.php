<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Collection;
use Filament\Tables\Table;
use App\Models\Ticket;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
                ->query(
                    Ticket::query()
                    ->when(auth()->user()->client_id, function ($query, $clientId) {
                        $query->where('client_id', $clientId);
                    })
                    ->orderByRaw("CASE WHEN status = 'open' THEN 1 ELSE 2 END ASC")
                    ->latest() // optional secondary sort by created_at
                )
                ->columns([
                    TextColumn::make('user.name')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('client.name')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('subject')
                        ->searchable(),
                    TextColumn::make('status')
                        ->searchable(),
                    TextColumn::make('resolved_at')
                        ->dateTime()
                        ->sortable(),
                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('deleted_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])
                ->filters([
                    TrashedFilter::make(),
                ])
                ->recordActions([
                    ViewAction::make(),
                ])
                ->toolbarActions([
                    BulkActionGroup::make([
                        DeleteBulkAction::make(),
                        ForceDeleteBulkAction::make(),
                        RestoreBulkAction::make(),
                    ]),
                ]);
    }
}
