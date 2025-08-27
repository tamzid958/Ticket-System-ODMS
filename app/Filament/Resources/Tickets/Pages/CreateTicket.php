<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Filament\Resources\Tickets\Schemas\TicketForm; // <-- Import TicketForm
use App\Models\Client;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Mail\SupportMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    /**
     * This hook runs before the form is filled. We use it to calculate
     * and set the initial value for the 'unit_value' field, which depends on other fields.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Set default values for the dependencies, which might not be in the form state yet.
        $data['ticket_weight'] ??= 1;
        $data['created_at'] ??= now();
        $data['client_id'] ??= Auth::user()->client_id ?: Client::first()?->id;

        // Create a temporary "get" closure that simulates the form's $get utility
        // by reading from our local $data array.
        $get = fn (string $key) => $data[$key] ?? null;

        // Calculate the initial value using the public method from TicketForm
        // and add it to the data that will fill the form.
        $data['unit_value'] = TicketForm::calculateUnitValue($get);

        return $data;
    }

    protected function afterCreate(): void
    {
        $ticket = $this->record;

        $subject = "New Ticket Created: {$ticket->subject}";

        $supporters = User::role('support')->get();

        foreach ($supporters as $supporter) {
            Mail::to($supporter->email)
                ->sendNow(new SupportMail($subject, $ticket));
        }
    }
}
