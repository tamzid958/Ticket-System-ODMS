<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Client;
use App\Enums\TicketStatus;

class TicketSummary extends Widget
{
    protected string $view = 'filament.widgets.ticket-summary';

    public $totalTickets = 0;
    public $usedTickets = 0;
    public $leftTickets = 0;
    public $pendingTickets = 0;

    public function mount(): void
    {
        $user = auth()->user();
        $client = $user->client;

        if ($client) {
            $this->totalTickets = $client->package?->monthly_quota ?? 0;

        // Only CLOSED tickets count as used
        $this->usedTickets = $client->tickets()
            ->whereMonth('created_at', now()->month)
            ->where('status', TicketStatus::CLOSED->value) // 'closed'
            ->count();

        // OPEN tickets count as pending
        $this->pendingTickets = $client->tickets()
            ->whereMonth('created_at', now()->month)
            ->where('status', TicketStatus::OPEN->value) // 'open'
            ->count();



            $this->leftTickets = max(0, $this->totalTickets - $this->usedTickets);
        }
    }
}
