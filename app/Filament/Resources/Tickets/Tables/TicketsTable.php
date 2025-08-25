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

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()
                    ->when(auth()->user()->hasRole('client'), function ($query) {
                        $query->where('client_id', auth()->user()->client_id);
                    })
                    ->orderByRaw("CASE WHEN status = 'open' THEN 1 ELSE 2 END ASC")
                    ->latest()
            )
            ->columns([
                TextColumn::make('user.name')->sortable(),
                TextColumn::make('client.name')->sortable(),
                TextColumn::make('subject')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('resolved_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
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
