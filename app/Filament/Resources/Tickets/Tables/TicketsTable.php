<?php

namespace App\Filament\Resources\Tickets\Tables;

use Illuminate\Support\Arr;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Ticket;
use Filament\Actions\Action;
use App\Enums\TicketStatus;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Select;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        $defaultMonth = now()->month;
        $defaultYear = now()->year;

        return $table
     ->query(function () {
                return Ticket::query()
                    ->when(auth()->user()->hasRole('client'), function ($query) {
                        $query->where('client_id', auth()->user()->client_id);
                    });

            })
            ->defaultSort('created_at', 'desc')
            ->columns(array_filter([

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if (auth()->user()->hasRole('client')) {
                            return $state;
                        }
                        return $state . ' (' . ($record->client?->name ?? 'No Client') . ')';
                    }),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn($record) => $record->subject),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->formatStateUsing(function ($state) {
                        return now()->parse($state)->format('Y-m-d') . "<br>" . now()->parse($state)->format('h:i A');
                    })
                    ->html()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->alignCenter()
                    ->size('xl')

                    ->color(function ($state) {
                        $value = $state instanceof TicketStatus ? $state->value : $state;

                        return match ($value) {
                            TicketStatus::CLOSED->value => 'success',
                            TicketStatus::PENDING->value => 'warning',
                            TicketStatus::OPEN->value => 'danger',
                            default => 'gray',
                        };
                    })
                    ->extraAttributes([
                        'class' => 'text-base font-semibold px-4 py-2',
                    ]),

                TextColumn::make('unit_value')
                    ->label('Unit Value')
                    ->alignCenter(),
                IconColumn::make('is_excess_unit')
                    ->label('Excess Unit')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->alignCenter(),
            ]))

            ->filters(
                array_filter([
                    // Date range filter

                    Filter::make('month')
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
                                ]),

                            Select::make('year')
                                ->label('Year')
                                ->options(fn() => array_combine(
                                    range(now()->year, now()->year - 5),
                                    range(now()->year, now()->year - 5)
                                )),

                                // Show client selector only to admins
                            auth()->user()->hasAnyRole(['super_admin', 'support',])
                            ? Select::make('client_id')
                                ->label('Client')
                                ->options(\App\Models\Client::pluck('name', 'id')->toArray())
                                ->placeholder('Select a Client') // <-- ADD THIS LINE
                            : null,
                        ])
                        ->query(function ($query, array $data) {
                            if (!empty($data['month']) && !empty($data['year'])) {
                                $from = Carbon::create($data['year'], $data['month'], 1)->startOfMonth();
                                $to = $from->copy()->endOfMonth();
                                $query->whereBetween('created_at', [$from, $to]);
                            }

                            if (!empty($data['client_id'])) {
                                $query->where('client_id', $data['client_id']);
                            }
                        })
                ])

            );



    }
}
