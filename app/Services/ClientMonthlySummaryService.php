<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ClientMonthlySummaryService
{
    public function getMonthlyClientSummary(int $client_id, int $year, int $month): array
    {
        /**
         * @var Collection|Ticket[] $tickets
         */
        $tickets = Ticket::query()
            ->where('client_id', $client_id)
            ->forMonth($year, $month)
            ->get();

        $costingDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $client = Client::findOrFail($client_id)->versionedInstance($costingDate);
        $package = $client->package->versionedInstance($costingDate);

        $unit_used = 0;
        $unit_discount = 0;
        $excess_unit_used = 0;

        if ($tickets->isNotEmpty()) {
            $unit_used = $tickets->sum('unit_value');
            $unit_discount = $tickets->sum('unit_discount');
            $excess_unit_used = $tickets->where('is_excess_unit', true)->sum('unit_value');
        }

        $monthly_quota = $package->monthly_quota ?? 0;
        $excess_per_unit_price = $package->excess_unit_price ?? 0;
        $package_price = $package->base_price ?? 0;
        $package_discount = $client->discount ?? 0;

        $excess_unit_cost = $excess_unit_used * $excess_per_unit_price;
        $total_cost = ($package_price - $package_discount) + $excess_unit_cost;


        return [
            'client_id' => $client_id,
            'package_id' => $package->id,
            'year' => $year,
            'month' => $month,

            'monthly_quota' => $monthly_quota,
            'excess_per_unit_price' => $excess_per_unit_price,
            'package_price' => $package_price,
            'package_discount' => $package_discount,

            'unit_used' => $unit_used,
            'excess_unit_used' => $excess_unit_used,
            'unit_discount' => $unit_discount,

            'excess_unit_cost' => $excess_unit_cost,
            'total_cost' => $total_cost,
        ];
    }


}
