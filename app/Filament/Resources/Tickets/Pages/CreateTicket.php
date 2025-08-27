<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Resources\Pages\CreateRecord;
use App\Mail\SupportMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function afterCreate(): void
    {
        $ticket = $this->record;

        $subject = "ODMS Ticket Creation: {$ticket->subject}";

        $supporters = User::role('support')->get();

        foreach ($supporters as $supporter) {
            Mail::to($supporter->email)
                ->send(new SupportMail($subject, $ticket));
        }
    }
}
