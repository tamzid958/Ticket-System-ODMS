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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = Auth::user();

        if ($user->hasRole('client') && $user->client_id) {
            $client = Client::find($user->client_id);
            $package = $client?->package ?? Package::first();

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

    protected function moveFilesToTicketFolder($ticket, array $formData): void
    {
        if (isset($formData['attachments']) && !empty($formData['attachments'])) {
            // Ensure the ticket-specific directory exists
            Storage::disk('public')->makeDirectory("ticket-attachments/{$ticket->id}");
            
            foreach ((array) $formData['attachments'] as $filePath) {
                $filename = basename($filePath);
                $newPath = "ticket-attachments/{$ticket->id}/{$filename}";

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->move($filePath, $newPath);
                }
            }
        }
    }

    protected function afterCreate(): void
    {
        $ticket = $this->record;
        $formData = $this->form->getState();

        $this->moveFilesToTicketFolder($ticket, $formData);

        $subject = "ODMS Ticket Creation: {$ticket->subject}";
        $supporters = User::role('support')->get();

        foreach ($supporters as $supporter) {
            Mail::to($supporter->email)
                ->send(new SupportMail($subject, $ticket));
        }
    }
}