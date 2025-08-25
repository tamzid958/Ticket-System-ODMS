<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Ticket;
use Carbon\Carbon;
use App\Services\IcsHolidayService;

class TicketUnitService
{
    protected Package $package;

    public function __construct(Package $package = null)
    {
        $this->package = $package ?? Package::first();
    }

    public function calculateTicketData(int $ticketWeight = 1, ?Carbon $ticketTime = null, ?int $clientId = null): array
    {
        $ticketTime = $ticketTime ?? Carbon::now('Asia/Dhaka');

        // Initialize calculations
        $baseUnit = $ticketWeight;
        $holidayMultiplier = 1;
        $timeslotMultiplier = 1;
        $isWeekendUnit = false;
        $unitDiscount = 0;

        // Check if holiday: Friday/Saturday OR ICS holiday
        $icsService = new IcsHolidayService($ticketTime->year);
        if (in_array($ticketTime->dayOfWeek, [5, 6]) || $icsService->isHoliday($ticketTime)) {
            $holidayMultiplier = $this->package->weekend_unit_multiplier;
            $isWeekendUnit = true;
        }

        // Apply the timing slot multiplier that matches the ticket time
        $timings = $this->package->timings ?? [];
        foreach ($timings as $slot) {
            $start = isset($slot['start_time']) ? Carbon::parse($slot['start_time'], 'Asia/Dhaka') : null;
            $end = isset($slot['end_time']) ? Carbon::parse($slot['end_time'], 'Asia/Dhaka') : null;

            if ($start && $end && $ticketTime->between($start, $end)) {
                $timeslotMultiplier = $slot['multiplier'] ?? 1;
                break; // only one slot applies
            }
        }

        // Calculate total unit value
        $unitValue = $baseUnit * $holidayMultiplier * $timeslotMultiplier;

        // Calculate unit discount if client is provided
        if ($clientId) {
            $unitDiscount = $this->calculateUnitDiscount($clientId, $unitValue, $ticketTime);
        }

        // Calculate total unit value
        $unitValue -= $unitDiscount;

        // Determine if this is excess unit
        $monthlyQuota = $this->package->monthly_quota;
        $currentMonthUsage = $this->getCurrentMonthUsage($clientId, $ticketTime);
        $isExcessUnit = ($currentMonthUsage + $unitValue) > $monthlyQuota;
        $isExcessUnit = $unitDiscount > 0 ? false : $isExcessUnit;
        $unitValue = $isExcessUnit ? 1 : $unitValue;
        return [
            'unit_value' => $unitValue,
            'holiday_multiplier' => $holidayMultiplier,
            'timeslot_multiplier' => $timeslotMultiplier,
            'is_weekend_unit' => $isWeekendUnit,
            'is_excess_unit' => $isExcessUnit,
            'unit_discount' => $unitDiscount,
        ];
    }

    protected function calculateUnitDiscount(?int $clientId, int $ticketUnitValue, Carbon $ticketTime): int
    {
        if (!$clientId) {
            return 0;
        }

        $monthlyQuota = $this->package->monthly_quota;
        $currentMonthUsage = $this->getCurrentMonthUsage($clientId, $ticketTime);

        // If current usage is already over quota, no discount
        if ($currentMonthUsage >= $monthlyQuota) {
            return 0;
        }

        // If current usage + new ticket doesn't exceed quota, no discount
        $totalAfterTicket = $currentMonthUsage + $ticketUnitValue;
        if ($totalAfterTicket <= $monthlyQuota) {
            return 0;
        }

        // Calculate discount: how much this ticket pushes over the quota
        $unitDiscount = $totalAfterTicket - $monthlyQuota;

        // Discount cannot exceed the ticket's unit value
        return min($unitDiscount, $ticketUnitValue);
    }

    protected function getCurrentMonthUsage(int $clientId, Carbon $date): int
    {
        return Ticket::where('client_id', $clientId)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->sum('unit_value');
    }

    public function calculateUnits(int $ticketWeight = 1, ?Carbon $ticketTime = null): float
    {
        $data = $this->calculateTicketData($ticketWeight, $ticketTime);
        return $data['unit_value'];
    }

    public function getUnitMessage(int $ticketWeight = 1, ?Carbon $date = null, ?int $clientId = null): string
    {
        $ticketTime = $date ?? Carbon::now('Asia/Dhaka');
        $data = $this->calculateTicketData($ticketWeight, $ticketTime, $clientId);
        $message = "";
        $steps = [];

        if (!$data['is_excess_unit']) {
            // Base unit
            $steps[] = "Base unit: $ticketWeight";

            // Holiday/Weekend multiplier
            if ($data['is_weekend_unit']) {
                $steps[] = "Holiday/Weekend multiplier: {$data['holiday_multiplier']}";
            } else {
                $steps[] = "Weekday multiplier: 1";
            }

            // Timeslot multiplier
            $steps[] = "Timeslot multiplier: {$data['timeslot_multiplier']}";

            $message = "Total units: {$data['unit_value']}. (" . implode(' × ', $steps) . ")";

            // Show discount if applicable
            if ($data['unit_discount'] > 0) {
                $message .= " Unit discount: {$data['unit_discount']} units.";
            }
        } else
        // Show excess info
        {
            $quota = $this->package->monthly_quota;
            $currentUsage = $this->getCurrentMonthUsage($clientId, $ticketTime);
            $excessUnits = ($currentUsage + $data['unit_value']) - $quota;
            $excessPrice = $this->package->excess_unit_price;
            $message .= " Exceeds package quota. Excess units: $excessUnits. Price: $excessPrice per unit.";
        }

        return $message;
    }
}