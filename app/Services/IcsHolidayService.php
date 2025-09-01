<?php

namespace App\Services;

use App\Models\HolidayIcs;
use Carbon\Carbon;
use ICal\ICal;

class IcsHolidayService
{
    private static ?self $instance = null;
    protected ICal $ical;

    private function __construct(int $year = null)
    {
        $year ??= Carbon::now()->year;
        $icsFileContent = $this->getIcsFileForYear($year);


        $this->ical = new ICal(false, [
            'defaultSpan' => 2,
            'defaultTimeZone' => 'Asia/Dhaka',
            'defaultWeekStart' => 'MO',
            'skipRecurrence' => false,
            'useTimeZoneWithRRules' => true,
        ]);


        $this->ical->initString($icsFileContent);

    }

    /** Singleton accessor */
    public static function getInstance(int $year = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($year);
        }

        return self::$instance;
    }

    /** Get ICS from DB or fallback to env link and save */
    protected function getIcsFileForYear(int $year): string
    {
        $record = HolidayIcs::where('year', $year)->first();

        if ($record) {
            return $record->ics_file;
        }

        $icsUrl = env('HOLIDAY_ICS_FILE', 'https://www.officeholidays.com/ics/bangladesh');
        $icsContent = file_get_contents($icsUrl);

        HolidayIcs::create([
            'year' => $year,
            'ics_file' => $icsContent,
        ]);

        return $icsContent;
    }

    /** Get all holidays of a year */
    public function getHolidays(int $year): array
    {
        $events = $this->ical->eventsFromRange("{$year}-01-01", "{$year}-12-31");

        return collect($events)->map(fn($event) => [
            'summary' => $event->summary,
            'date' => Carbon::parse($event->dtstart_array[3])->toDateString(),
        ])->toArray();
    }

    /** Check if date is holiday (Friday/Saturday automatically counted) */
    public function isHoliday(Carbon $date): bool
    {

        if (in_array($date->dayOfWeek, [5, 6])) {
            return true;
        }

        $holidays = $this->getHolidays($date->year);


        return collect($holidays)->contains(fn($holiday) => $holiday['date'] === $date->toDateString());
    }
}
