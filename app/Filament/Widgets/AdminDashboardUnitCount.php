<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\Client;
use Carbon\Carbon;

class AdminDashboardUnitCount extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'adminDashboardUnitCount';

    /**
     * Widget Title
     *
     * @var string|null
     */
    public function getHeading(): ?string
    {
        $now = Carbon::now('Asia/Dhaka');
        $monthYear = $now->format('F Y');

        return "Monthly used units for clients [{$monthYear}]";
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
            $monthly_used_units = [];
            $colors = [];

            foreach ($clients as $client) {
                // $client = $client->$client->versionedInstance();
                $clientNames[] = $client->name;

                $monthlyTickets = Ticket::query()
                ->where('client_id', $client->id)
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->get();

                $total_tickets = $monthlyTickets->count();
                $extra_units = $monthlyTickets->where('is_excess_unit', true)->count();
                $used_units = $monthlyTickets->sum('unit_value') - $extra_units;

                $monthly_used_units[] = max($used_units, 0);

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
                    'name' => 'BasicBarChart',
                    'data' => $monthly_used_units,
                ],
            ],
            'xaxis' => [
                'categories' => $clientNames,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
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
