<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Client;
use App\Enums\TicketStatus;

class TicketCostSummary extends Widget
{
    protected string $view = 'filament.widgets.ticket-cost-summary';

    public $basePrice = 0.0;
    public $extraPrice = 0.0;
    public $totalPrice = 0.0;

    public function mount(): void
    {
        $user = auth()->user();
        $client = $user->client;

        if ($client && $client->package) {
            $package = $client->package;

            // Count used tickets (CLOSED tickets for this month)
            $usedTickets = $client->tickets()
                ->whereMonth('created_at', now()->month)
                ->where('status', TicketStatus::CLOSED->value)
                ->count();

            // Base price
            $this->basePrice = $package->base_price;

            // Extra tickets beyond monthly quota
            $extraTickets = max(0, $usedTickets - $package->monthly_quota);
            $this->extraPrice = $extraTickets * $package->excess_unit_price;

            // Total
            $this->totalPrice = $this->basePrice + $this->extraPrice;
        }
    }
}
