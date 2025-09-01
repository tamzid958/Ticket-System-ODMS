<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientMail;
use App\Enums\TicketStatus;
use App\Enums\MailStatus;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make("Edit"),
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
                        'resolved_by' => auth()->id(),
                    ]);

                    if ($record->client) {
                        foreach ($record->client->users as $user) {
                            if ($user->email) {
                                \App\Models\Mail::create([
                                    'user_id' => $user->id,
                                    'ticket_id' => $record->id,
                                    'status' => MailStatus::PENDING,
                                ]);
                                Mail::to($user->email)
                                    ->queue(new ClientMail(
                                        subjectLine: 'ODMS Ticket Resolved: ' . $record->subject,
                                        ticket: $record,
                                        userId: $user->id
                                    ));
                            }
                        }
                    }
                }),
        ];
    }
}