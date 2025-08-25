<?php

namespace App\Services;

use App\Models\HolidayIcs;
use Carbon\Carbon;
use ICal\ICal;
use Illuminate\Support\Facades\Http;

class IcsHolidayService
{
    protected ICal $ical;

    public function __construct(int $year = null)
    {
        $year ??= Carbon::now()->year;
        $icsFileContent = $this->getIcsFileForYear($year);

        // TODO: save to temp file because ICal lib needs a file path,
        // we can delete the file later in the method. need to discuss
        $tempPath = storage_path("temp_holidays_{$year}.ics");
        file_put_contents($tempPath, $icsFileContent);
        
       $this->ical = new ICal($tempPath, [
            'defaultSpan'           => 2,
            'defaultTimeZone'       => 'Asia/Dhaka',
            'defaultWeekStart'      => 'MO',
            'skipRecurrence'        => false,
            'useTimeZoneWithRRules' => true,
        ]);

    }

    /** Get ICS from DB or fallback to env link and save */
    protected function getIcsFileForYear(int $year): string
    {
        $record = HolidayIcs::where('year', $year)->first();

        if ($record) {
            return $record->ics_file;
        }

        $icsUrl = env('HOLIDAY_ICS_FILE');
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
            'date'    => Carbon::parse($event->dtstart_array[3])->toDateString(),
        ])->toArray();
    }

    /** Check if date is holiday (Friday/Saturday automatically counted) */
    public function isHoliday(Carbon $date): bool
    {
        // Friday/Saturday automatic check
        if (in_array($date->dayOfWeek, [5, 6])) { // Friday=5, Saturday=6
            return true;
        }

        $holidays = $this->getHolidays($date->year);

        return collect($holidays)->contains(fn ($holiday) => $holiday['date'] === $date->toDateString());
    }
}
