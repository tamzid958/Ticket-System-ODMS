<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\Client;
use Carbon\Carbon;

class AdminDashboard extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'adminDashboard';

    public function getHeading(): ?string
    {
        $now = Carbon::now('Asia/Dhaka');
        $monthYear = $now->format('F Y'); // e.g., August 2025

        return "Monthly bill for clients [{$monthYear}]";
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */

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
            $clients = Client::with('package')->get();

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

                $total_tickets = $monthlyTickets->count();
                $extra_units = $monthlyTickets->where('is_excess_unit', true)->count();
                $used_units = $monthlyTickets->sum('unit_value') - $extra_units;

                $package = $client->package->versionedInstance();
                $packageName = $package->name ?? 'N/A';
                $packageLimit = $package->monthly_quota ?? 0;


                $total_monthly_bill = $package->base_price + $extra_units * $package->excess_unit_price - $client->discount;

                $monthlyBills[] = max($total_monthly_bill, 0);

                // diff colors for diff clients
                $hue = rand(0, 360);          
                $saturation = rand(50, 90);
                $lightness = rand(40, 70);
                $colors[] = "hsl($hue, $saturation%, $lightness%)";

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
                    'distributed' => true,
                ],
            ],

        ];
    }
}
