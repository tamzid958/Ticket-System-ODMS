<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use App\Enums\TicketStatus;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resolve')
                ->label('Resolve Ticket')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn ($record) => $record->status !== TicketStatus::CLOSED)
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => TicketStatus::CLOSED,
                        'resolved_at' => now(),
                        'resolved_by' => auth()->id(), // current user ID
                    ]);
                }),
        ];
    }
}
