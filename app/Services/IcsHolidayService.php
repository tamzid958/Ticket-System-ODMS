<?php

namespace App\Services;

use Carbon\Carbon;
use ICal\ICal;

class IcsHolidayService
{
    protected ICal $ical;

    public function __construct()
    {
        $icsFile = env('HOLIDAY_ICS_FILE'); // path or URL
        $this->ical = new ICal($icsFile, [
            'defaultSpan'           => 2,
            'defaultTimeZone'       => 'UTC',
            'defaultWeekStart'      => 'MO',
            'skipRecurrence'        => false,
            'useTimeZoneWithRRules' => true,
        ]);
    }

    /**
     * Get all holidays of a given year
     */
    public function getHolidays(int $year): array
    {
        $events = $this->ical->eventsFromRange("{$year}-01-01", "{$year}-12-31");

        return collect($events)->map(fn($event) => [
            'summary' => $event->summary,
            'date'    => Carbon::parse($event->dtstart_array[3])->toDateString(),
        ])->toArray();
    }

    /**
     * Check if given date is holiday
     */
    public function isHoliday(Carbon $date): bool
    {
        return collect($this->getHolidays($date->year))
            ->contains(fn ($holiday) => $holiday['date'] === $date->toDateString());
    }
}
