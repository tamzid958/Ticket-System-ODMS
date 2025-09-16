<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketResolvedMail;
use App\Enums\TicketStatus;
use App\Enums\MailStatus;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    /**
     * @throws \Exception
     */
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
                ->modalHeading('Resolve Ticket')
                ->modalDescription('Are you sure you want to resolve this ticket?')
                ->modalWidth(fn ($record) => auth()->user()->hasAnyRole(['support', 'super_admin']) ? '4xl' : 'lg')
                ->schema([
                    RichEditor::make('solution')
                        ->label('How did you solve it?')
                        ->required(fn () => auth()->user()->hasAnyRole(['support']))
                        ->visible(fn () => auth()->user()->hasAnyRole(['support', 'super_admin']))
                        ->columnSpanFull()
                        ->extraAttributes(['style' => 'min-height: 200px;'])
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory(fn (callable $get, $record) => "ticket-solutions/{$record->id}")
                        ->fileAttachmentsVisibility('public'),
                ])
                ->action(function (array $data, $record) {
                    $record->update([
                        'status' => TicketStatus::CLOSED,
                        'resolved_at' => now(),
                        'resolved_by' => auth()->id(),
                        'solution' => $data['solution'] ?? null,
                    ]);

                    Log::info("Ticket Resolved.",
                        [
                            'ticket_id' => $record->id,
                            'resolved_by' => [
                                'id' => auth()->id(),
                                'name' => auth()->user()->name,
                            ],
                            'status' => TicketStatus::CLOSED,
                        ]);

                    $this->sendTicketResolvedMail($record);
                }),
            Action::make('reopen')
                ->label('Reopen Ticket')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn ($record) => $record->status === TicketStatus::CLOSED && auth()->user()->hasAnyRole(['support', 'super_admin']))
                ->requiresConfirmation()
                ->modalHeading('Reopen Ticket')
                ->modalDescription('Are you sure you want to reopen this ticket?')
                ->action(function (array $data, $record) {
                    $record->update([
                        'status' => TicketStatus::OPEN,
                        'resolved_at' => null,
                        'resolved_by' => null,
                    ]);

                    Log::info("Ticket Reopened.",
                        [
                            'ticket_id' => $record->id,
                            'reopened_by' => [
                                'id' => auth()->id(),
                                'name' => auth()->user()->name,
                            ],
                            'status' => TicketStatus::OPEN,
                        ]);
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
