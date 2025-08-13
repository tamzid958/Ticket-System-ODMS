<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TimePicker;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('monthly_quota')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('per_unit_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('excess_unit_multiplier')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('weekend_unit_multiplier')
                    ->required()
                    ->numeric()
                    ->default(2),
                Repeater::make('timings')
                    ->label('24 Hour Segments')
                    ->schema([
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->withoutSeconds(),

                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->withoutSeconds()
                            ->rule(function (Closure $get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    $start = $get('start_time');
                                    if ($start && $value && $value <= $start) {
                                        $fail('End time must be after start time.');
                                    }
                                };
                            }),

                        TextInput::make('multiplier')
                            ->label('Multiplier')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->minItems(1)
                    ->columnSpanFull()
                    ->addActionLabel('Add Time Slot')
                    ->rule(function (Closure $get) {
                        return function (string $attribute, $value, Closure $fail) {
                            if (!is_array($value)) {
                                return;
                            }

                            // Convert times to comparable numbers (minutes since midnight)
                            $toMinutes = fn($time) => $time ? (int) date('H', strtotime($time)) * 60 + (int) date('i', strtotime($time)) : null;

                            $slots = [];
                            foreach ($value as $index => $row) {
                                $start = $toMinutes($row['start_time'] ?? null);
                                $end   = $toMinutes($row['end_time'] ?? null);

                                if ($start !== null && $end !== null) {
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
                        };
                    })
                    ]);
    }
}
