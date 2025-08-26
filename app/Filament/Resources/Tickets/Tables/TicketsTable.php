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

use Filament\Actions\Action;
use App\Enums\TicketStatus;
use App\Services\TicketUnitService;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function () {
                return Ticket::query()
                    ->when(auth()->user()->hasRole('client'), function ($query) {
                        $query->where('client_id', auth()->user()->client_id);
                    })
                    ->orderByRaw("CASE WHEN status = 'open' THEN 1 ELSE 2 END ASC")
                    ->latest();
            })
            ->columns(array_filter([
                TextColumn::make('user.name')->sortable(),

                // Only show client column if user does NOT have client_id
                auth()->user()->client_id ? null : TextColumn::make('client.name')->sortable(),

                TextColumn::make('subject')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('resolved_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),

                // Unit Value Column with icon tooltip
TextColumn::make('unit_value')
    ->label('Unit Value')
    ->formatStateUsing(function ($record) {
        $message = $record->getUnitMessage();

        return $record->unit_value 
            . ' ' // gap between value and icon
            . '<span title="' . e($message) . '" style="display:inline-block; margin-left:0.5rem; color:#9CA3AF; font-size:1.2em; ">&#9432;</span>';
    })
    ->html()
    ->sortable(),





                // Is Excess Unit Column
                TextColumn::make('is_excess_unit')
                    ->label('Excess Unit')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->sortable(),
            ]))
            ->recordActions([
                ViewAction::make(),
                Action::make('resolve')
                    ->label('Resolve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== TicketStatus::CLOSED)
                    ->action(function ($record) {
                        $record->update([
                            'status' => TicketStatus::CLOSED,
                            'resolved_at' => now(),
                        ]);
                    }),
            ]);
    }
}
