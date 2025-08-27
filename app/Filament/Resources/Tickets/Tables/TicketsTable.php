<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Ticket;
use Filament\Actions\Action;
use App\Enums\TicketStatus;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Select;
class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function () {
                return Ticket::query()
                    ->when(auth()->user()->hasRole('client'), function ($query) {
                        $query->where('client_id', auth()->user()->client_id);
                    })
                    ->orderByRaw("CASE WHEN status = 'open' THEN 1 ELSE 2 END ASC")
                    ->orderBy('created_at', 'desc')

                    ->latest();
            })
            ->columns(array_filter([
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        // $state is user name
                        if (auth()->user()->hasRole('client')) {
                            return $state; // just username
                        }
                        return $state . ' (' . ($record->client?->name ?? 'No Client') . ')';
                    }),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(25)  // truncate to 25 characters
                    ->tooltip(fn($record) => $record->subject), // optional: full text on hover

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->formatStateUsing(function ($state) {
                        return now()->parse($state)->format('h:i A') . "<br>" . now()->parse($state)->format('Y-m-d');
                    })
                    ->html()
                    ->sortable(),     
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->alignCenter()
                    ->size('xl') // small, md, lg, xl

                    ->color(function ($state) {
                        // Handle both enum objects and string values
                        $value = $state instanceof TicketStatus ? $state->value : $state;

                        return match ($value) {
                            TicketStatus::CLOSED->value => 'success',   // green
                            TicketStatus::PENDING->value => 'warning',  // yellow
                            TicketStatus::OPEN->value => 'danger',      // red
                            default => 'gray',
                        };
                    })
                    ->extraAttributes([
                        'class' => 'text-base font-semibold px-4 py-2', // bigger font & padding
                    ]),

                TextColumn::make('unit_value')
                    ->label('Unit Value')
                    ->alignCenter(),
                IconColumn::make('is_excess_unit') // use IconColumn instead of TextColumn
                    ->label('Excess Unit')
                    ->boolean() // true/false
                    ->trueIcon('heroicon-o-check') // check icon
                    ->falseIcon('heroicon-o-x-mark')
                    ->alignCenter(),               // center the icon
            ]))


            ->filters([
                Filter::make('month_year')
                    ->form([
                        Select::make('month')
                            ->label('Month')
                            ->options([
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->default(now()->month)
                            ->required(),

                        Select::make('year')
                            ->label('Year')
                            ->options(function () {
                                $currentYear = now()->year;
                                return array_combine(range($currentYear, $currentYear - 5), range($currentYear, $currentYear - 5));
                            })
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['month']) && !empty($data['year'])) {
                            $query->whereYear('created_at', $data['year'])
                                ->whereMonth('created_at', $data['month']);
                        }
                    }),
            ]);


    }
}
