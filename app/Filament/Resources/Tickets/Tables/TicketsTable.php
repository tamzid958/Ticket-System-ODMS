<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Enums\TicketStatus;
use App\Models\Client;
use App\Models\Ticket;
use Carbon\Carbon;
use Exception;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class TicketsTable
{
    /**
     * @throws Exception
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function () {
                return Ticket::query()
                    ->when(auth()->user()->hasRole('client'), function ($query) {
                        $query->where('client_id', auth()->user()->client_id);
                    });

            })
            ->defaultSort('created_at', 'desc')
            ->columns(array_filter([
                TextColumn::make('subject')
                    ->searchable()
                    ->wrap()
                    ->formatStateUsing(function ($state, Ticket $record) {
                        if (!auth()->user()->hasRole('client')) {
                            return "[{$record->client?->name}] " . $state;
                        }

                        return $state;
                    })
                    ->description(function (Ticket $record): string {
                        $createdBy = $record->user?->name ?? 'N/A';
                        $createdAt = $record->created_at?->format('d M Y, h:i A') ?? 'N/A';

                        $clientName = $record->user?->client?->name;

                        if (!auth()->user()->hasRole('client') && $clientName) {
                            return "Created by $createdBy ($clientName) at $createdAt";
                        }

                        return "Created by $createdBy at $createdAt";
                    })
                    ->sortable(query: fn($query, string $direction) => $query->orderBy('created_at', $direction)),
                TextColumn::make('unit_value')
                    ->label('Tags')
                    ->state(function (Ticket $record): array {
                        return array_filter([
                            "Unit Value: $record->unit_value",
                            $record->is_excess_unit ? 'Excess' : null,
                            $record->is_weekend_unit ? 'Holiday' : null,
                        ]);
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Excess' => 'danger',
                        'Holiday' => 'primary',
                        default => 'gray',
                    })
                    ->size(TextSize::Large)
                    ->alignStart()
//                    ->listWithLineBreaks()
                    ->verticallyAlignCenter(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->alignCenter()
                    ->size(TextSize::Large)
//                    ->tooltip(function (TicketStatus $state, Ticket $record): ?string {
//                        if ($state == TicketStatus::CLOSED) {
//                            $resolvedBy = $record->resolver?->name ?? 'N/A';
//                            $resolvedAt = $record->resolved_at?->format('d M Y, h:i A') ?? 'N/A';
//                            return "Resolved by $resolvedBy at $resolvedAt";
//                        }
//
//                        return null;
//                    })
                    ->extraAttributes([
                        'class' => 'text-base font-semibold px-4 py-2',
                    ]),
            ]))
            ->recordActions([
                ViewAction::make(),
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
                                ->options(Client::pluck('name', 'id')->toArray())
                                ->placeholder('Select a Client') // <-- ADD THIS LINE
                                : null,
                        ])
                        ->query(function ($query, array $data) {
                            if (!empty($data['month']) && !empty($data['year'])) {
                                $from = Carbon::create($data['year'], $data['month'])->startOfMonth();
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
