<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Ticket;
use Carbon\Carbon;

class TicketUnitService
{
    protected Package $package;

    public function __construct(Package $package = null)
    {
        $this->package = $package ?? Package::first();
    }

    public function calculateTicketData(int $ticketWeight = 1, ?Carbon $ticketTime = null, ?int $clientId = null): array
    {
        $ticketTime ??=  Carbon::now();
        $icsService = IcsHolidayService::getInstance($ticketTime->year);

        $isWeekendUnit = $icsService->isHoliday($ticketTime);
        $holidayMultiplier = $isWeekendUnit ? $this->package->weekend_unit_multiplier : 1;
        $timeslotMultiplier = $this->getTimeslotMultiplier($ticketTime);
        $unitValue = $ticketWeight * $holidayMultiplier * $timeslotMultiplier;
        $unitDiscount = $clientId ? $this->calculateUnitDiscount($clientId, $unitValue, $ticketTime) : 0;
        $unitValue -= $unitDiscount;

        $isExcessUnit = $this->checkExcessUnit($clientId, $unitValue, $ticketTime, $unitDiscount);
        if ($isExcessUnit) $unitValue = 1;

        return [
            'unit_value'          => $unitValue,
            'holiday_multiplier'  => $holidayMultiplier,
            'timeslot_multiplier' => $timeslotMultiplier,
            'is_weekend_unit'     => $isWeekendUnit,
            'is_excess_unit'      => $isExcessUnit,
            'unit_discount'       => $unitDiscount,
        ];
    }

    protected function getTimeslotMultiplier(Carbon $ticketTime): float
    {
        $ticketTimeOnly = $ticketTime->format('H:i');

        foreach ($this->package->timings ?? [] as $slot) {

            $start = $slot['start_time'] ?? null;
            $end = $slot['end_time'] ?? null;

            if ($start && $end) {
                $startTime = Carbon::createFromFormat('H:i', $start);
                $endTime = Carbon::createFromFormat('H:i', $end);
                $ticketTimeCarbon = Carbon::createFromFormat('H:i', $ticketTimeOnly);


                if ($ticketTimeCarbon->between($startTime, $endTime->addMinute())) {
                    return $slot['multiplier'] ?? 1;
                }
            }
        }

        return 1;
    }



    protected function calculateUnitDiscount(int $clientId, int $unitValue, Carbon $ticketTime): int
    {
        $monthlyQuota = $this->package->monthly_quota;
        $usage = Ticket::where('client_id', $clientId)
            ->whereYear('created_at', $ticketTime->year)
            ->whereMonth('created_at', $ticketTime->month)
            ->sum('unit_value');

        if ($usage >= $monthlyQuota) return 0;
        $overflow = ($usage + $unitValue) - $monthlyQuota;
        return max(0, min($unitValue, $overflow));
    }

    protected function checkExcessUnit(?int $clientId, int $unitValue, Carbon $ticketTime, int $unitDiscount): bool
    {
        if (!$clientId || $unitDiscount > 0) return false;
        $monthlyQuota = $this->package->monthly_quota;
        $usage = Ticket::where('client_id', $clientId)
            ->whereYear('created_at', $ticketTime->year)
            ->whereMonth('created_at', $ticketTime->month)
            ->sum('unit_value');
        return ($usage + $unitValue) > $monthlyQuota;
    }

    public function getUnitMessage(int $ticketWeight = 1, ?Carbon $date = null, ?int $clientId = null): string
    {
        $data = $this->calculateTicketData($ticketWeight, $date, $clientId);
        if ($data['is_excess_unit']) {
            return "Exceeds quota. Units: {$data['unit_value']}.";
        }

        $message = "Total units: {$data['unit_value']} (Base: $ticketWeight × Holiday: {$data['holiday_multiplier']} × Timeslot: {$data['timeslot_multiplier']})";
        if ($data['unit_discount'] > 0) $message .= " | Discount: {$data['unit_discount']}";
        return $message;
    }

    public function extractTotalUnitsFromUnitMessage(string $message): ?int
    {
        if (preg_match('/Total units:\s*(\d+)/', $message, $m)) return (int)$m[1];
        if (preg_match('/Exceeds quota\. Units:\s*(\d+)/i', $message, $matches)) return (int) $matches[1];

        return null;
    }
}
