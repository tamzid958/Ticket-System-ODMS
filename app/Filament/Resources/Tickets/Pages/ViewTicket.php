<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Mail\TicketCreatedMail;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResolvedMail;
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

                    $this->sendTicketResolvedMail($record);
                }),
        ];
    }

    /**
     * @description Sends ticket resolved email to ticket creator with relevant client & support users in BCC
     * @param Ticket $ticket
     * @return void
     */
    protected function sendTicketResolvedMail(Ticket $ticket): void
    {
        $recipient = $ticket->user;

        $clientUsers = $ticket->client->users;
        $supporters = User::role('support')->get();

        $bcc = $supporters->merge($clientUsers)
            ->reject(fn($user) => $user->id === $recipient->id);

        Mail::to($recipient)
            ->bcc($bcc)
            ->send(new TicketResolvedMail($ticket));

        Log::info('Ticket Resolved Mail Sent.', [
            'ticket_id' => $ticket->id,
            'to'        => $recipient->email,
            'bcc'       => $bcc->pluck('email')->toArray(),
        ]);
    }
}
