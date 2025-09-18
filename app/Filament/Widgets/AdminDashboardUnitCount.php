<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\Client;
use Carbon\Carbon;
use App\Utils\AppUtil;

class AdminDashboardUnitCount extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'adminDashboardUnitCount';

    /**
     * Disable polling / auto-refresh
     *
     * @var string|null
     */
    protected ?string $pollingInterval = null;

    /**
     * Widget Title
     *
     * @var string|null
     */
    public function getHeading(): ?string
    {
        $now = Carbon::now('Asia/Dhaka');
        $monthYear = $now->format('F Y');

        return "Tickets - {$monthYear}";
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'support',]);
    }

    public function getHeaderWidgetsColumns(): int|string|array
    {
        return 2;
    }

    protected int | string | array $columnSpan = 1;

    protected function getOptions(): array
    {
        $now = Carbon::now('Asia/Dhaka');
        $clients = Client::with('package')->orderBy('name')->get();

        $clientNames = [];
        $monthly_used_units = [];
        $colors = [];

        foreach ($clients as $client) {
            $client = $client->versionedInstance();
            $clientNames[] = $client->name;

            $monthlyTickets = Ticket::query()
                ->where('client_id', $client->id)
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->get();

            $used_units = $monthlyTickets->sum('unit_value');
            $monthly_used_units[] = max($used_units, 0);

            // deterministic color based on client ID
            $colors[] = AppUtil::generateColorCode((string)$client->name);
        }

        return AppUtil::getDonutChartConfig($clientNames, $monthly_used_units, $colors);
    }
}
