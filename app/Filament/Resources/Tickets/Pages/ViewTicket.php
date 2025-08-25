<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

use Filament\Actions\Action;
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
                ->action(function ($record) {
                    $record->update([
                        'status' => TicketStatus::CLOSED,
                        'resolved_at' => now(),
                    ]);
                }),
        ];
    }
}
