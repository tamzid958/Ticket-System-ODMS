<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use App\Mail\SupportMail;
use App\Models\User;
use App\Models\Client;
use App\Models\Package;
use App\Services\TicketUnitService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Enums\MailStatus;
use Illuminate\Support\Facades\Mail;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = Auth::user();


        if ($user->hasRole('client') && $user->client_id) {


            $client = Client::find($user->client_id)->versionedInstance();
            $package = $client?->package ?? Package::first()->versionedInstance();

            if ($package) {
                $service = new TicketUnitService($package);
                $ticketWeight = 1;
                $createdAt = now();

                $message = $service->getUnitMessage($ticketWeight, $createdAt, $user->client_id);
                $data['unit_value'] = $service->extractTotalUnitsFromUnitMessage($message);
            }
        } else {

            $data['unit_value'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $ticket = $this->record;


        $subject = "ODMS Ticket Creation: {$ticket->subject}";

        $supporters = User::role('support')->get();

        foreach ($supporters as $supporter) {
            \App\Models\Mail::create([
                'user_id' => $supporter->id,
                'ticket_id' => $ticket->id,
                'status' => MailStatus::PENDING,
            ]);

            Mail::to($supporter->email)
                ->queue(new SupportMail($subject, $ticket, $supporter->id));
        }
    }
}
