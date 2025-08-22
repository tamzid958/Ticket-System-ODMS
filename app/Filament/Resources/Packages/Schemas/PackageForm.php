<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Slider;
use Filament\Support\Enums\Alignment;
use Closure;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('monthly_quota')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('base_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('excess_unit_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('Excess Unit Price'),

                Slider::make('weekend_unit_multiplier')
                    ->required()
                    ->default(1)
                    ->range(minValue: 0, maxValue: 10)
                    ->step(1)
                    ->tooltips(),
                Repeater::make('timings')
                    ->label('Time Slots')
                    ->grid(3)
                    ->addActionAlignment(Alignment::Start)
                    ->schema([
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->withoutSeconds()
                            ->columnSpan(1),

                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->withoutSeconds()
                            ->columnSpan(1),

                        Slider::make('multiplier')
                            ->label('Multiplier')
                            ->default(1)
                            ->range(minValue: 0, maxValue: 10)
                            ->step(1)
                            ->required()
                            ->tooltips()
                            ->columnSpanFull(),
                    ])
                    ->minItems(1)
                    ->columnSpanFull()
                    ->addActionLabel('Add Time Slot')
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if (!is_array($value)) {
                                return;
                            }

                            // Convert times to comparable numbers (minutes since midnight)
                            $toMinutes = function ($time) {
                                if (!is_string($time) || trim($time) === '') {
                                    return null;
                                }
                                $timestamp = strtotime($time);
                                if ($timestamp === false) {
                                    return null;
                                }
                                return (int) date('H', $timestamp) * 60 + (int) date('i', $timestamp);
                            };

                            $slots = [];

                            $rowNumber = 0;

                            foreach ($value as $index => $row) {
                                $rowNumber++;
                                $start = $toMinutes($row['start_time'] ?? null);
                                $end   = $toMinutes($row['end_time'] ?? null);

                                // Ensure start and end times are present
                                if ($start !== null && $end !== null) {

                                    // ✅ Check if end time is before start time
                                    if ($end <= $start) {
                                        $fail("Row " . $rowNumber . ": End time must be after start time.");
                                        return;
                                    }

                                    // Check for overlaps
                                    foreach ($slots as [$s, $e]) {
                                        if (
                                            ($start >= $s && $start < $e) || // start inside another slot
                                            ($end > $s && $end <= $e) ||     // end inside another slot
                                            ($start <= $s && $end >= $e)     // overlaps completely
                                        ) {
                                            $fail('Time slots must not overlap.');
                                            return;
                                        }
                                    }
                                    $slots[] = [$start, $end];
                                }
                            }

                            // Check for 24h coverage (0 to 1440 minutes)
                            usort($slots, fn($a, $b) => $a[0] <=> $b[0]);
                            $current = 0;
                            foreach ($slots as [$s, $e]) {
                                if ($s > $current + 1) {
                                    $fail('Time slots must cover the full 24 hours without gaps.');
                                    return;
                                }
                                $current = max($current, $e);
                            }
                            // Check if the last slot ends at or after 1440 minutes (24 hours)
                            // If the last slot ends before 1440, it means there's a gap
                            // between the last slot and the end of the day
                            if ($current + 1 < 1440) {
                                $fail('Time slots must cover the full 24 hours without gaps.');
                            }

                        },
                    ])
        ]);
    }
}
