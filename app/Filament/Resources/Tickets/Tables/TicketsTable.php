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
        // Store default filter values
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
                        // $state is user name
                        if (auth()->user()->hasRole('client')) {
                            return $state; // just username
                        }
                        return $state . ' (' . ($record->client?->name ?? 'No Client') . ')';
                    }),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(25) // truncate to 25 characters
                    ->tooltip(fn($record) => $record->subject), // optional: full text on hover

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
                    ->size('xl') // small, md, lg, xl

                    ->color(function ($state) {
                        // Handle both enum objects and string values
                        $value = $state instanceof TicketStatus ? $state->value : $state;

                        return match ($value) {
                            TicketStatus::CLOSED->value => 'success', // green
                            TicketStatus::PENDING->value => 'warning', // yellow
                            TicketStatus::OPEN->value => 'danger', // red
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
                    ->alignCenter(), // center the icon
            ]))
            ->headerActions([
                Action::make('downloadReport')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    // --- ADD THE FOLLOWING TWO LINES ---
                    ->disabled(
                        fn($livewire): bool =>
                            // Disable if the user is an admin AND no client is selected in the filter.
                        auth()->user()->client_id === null && empty($livewire->tableFilters['month']['client_id'])
                    )
                    ->action(function (Action $action, $livewire) {
                        // Get month and year, with defaults
                        $month = Arr::get($livewire->tableFilters, 'month.month', now()->month);
                        $year = Arr::get($livewire->tableFilters, 'month.year', now()->year);

                        // Determine the Client ID (this logic is still good as a fallback)
                        $clientId = auth()->user()->client_id ?: Arr::get($livewire->tableFilters, 'month.client_id');

                        // This server-side check is still a good safety net
                        if (empty($clientId)) {
                            Notification::make()
                                ->title('Client Not Selected')
                                ->body('Please select a client from the filters to generate a report.')
                                ->danger()
                                ->send();
                            $action->halt();
                            return;
                        }

                        // If client selected, redirect to PDF route
                        return redirect()->route('reports.tickets.download', [
                            'client' => $clientId,
                            'year' => $year,
                            'month' => $month,
                        ]);
                    })
                    ->requiresConfirmation(false),
            ])



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
                                ])
                                ->default(now()->month)
                                ->required(),

                            Select::make('year')
                                ->label('Year')
                                ->options(fn() => array_combine(
                                    range(now()->year, now()->year - 5),
                                    range(now()->year, now()->year - 5)
                                ))
                                ->default(now()->year)
                                ->required(),

                                // Show client selector only to admins
                            auth()->user()->client_id === null
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