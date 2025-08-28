<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\Client;
use Carbon\Carbon;
use App\Utils\AppUtil;

class AdminDashboard extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'adminDashboard';

    /**
     * Disable automatic widget polling (optional)
     */
protected ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        $now = Carbon::now('Asia/Dhaka');
        $monthYear = $now->format('F Y'); // e.g., August 2025

        return "Monthly bill for clients [{$monthYear}]";
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && !$user->client()->exists();
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    protected function getOptions(): array
    {
        $now = Carbon::now('Asia/Dhaka');

        // Stable ordering by ID ensures colors stay consistent
        $clients = Client::with('package')->orderBy('name')->get();

        $clientNames = [];
        $monthlyBills = [];
        $colors = [];

        foreach ($clients as $client) {
            $client = $client->versionedInstance();
            $clientNames[] = $client->name;

            $monthlyTickets = Ticket::query()
                ->where('client_id', $client->id)
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->get();

            $extra_units = $monthlyTickets->where('is_excess_unit', true)->count();

            $package = $client->package->versionedInstance();

            $total_monthly_bill = $package->base_price + $extra_units * $package->excess_unit_price - $client->discount;
            $monthlyBills[] = max($total_monthly_bill, 0);

            // Deterministic color per client ID
            $colors[] = AppUtil::generateColorCode((string)$client->name);
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 400,
            ],
            'series' => [
                [
                    'name' => 'Bill:',
                    'data' => $monthlyBills,
                ],
            ],
            'xaxis' => [
                'categories' => $clientNames,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => $colors,
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 5,
                    'horizontal' => false,
                    'distributed' => true, // one color per bar
                ],
            ],
        ];
    }
}
