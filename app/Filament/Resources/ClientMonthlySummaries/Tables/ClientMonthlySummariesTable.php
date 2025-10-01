<?php

namespace App\Filament\Resources\ClientMonthlySummaries\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class ClientMonthlySummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->orderByDesc('year')
                      ->orderByDesc('month');

                // Restrict to client if user is a client
                if (auth()->user()->hasAnyRole(['client',])) {
                    $query->where('client_id', auth()->user()->client_id);
                }
            })
            ->columns([
                TextColumn::make('client.name')
                    ->sortable()
                    ->label('Client')
                    ->searchable(),

                TextColumn::make('month_year')
                    ->label('Month')
                    ->getStateUsing(
                        fn ($record) => Carbon::createFromDate($record->year, $record->month, 1)->format('M, Y')
                    )
                    ->searchable(),

                TextColumn::make('package_info')
                    ->label('Package Info')
                    ->getStateUsing(function ($record) {
                        return sprintf(
                            "%s | Quota: %s | Price: %s | Discount: %s",
                            $record->package?->name ?? '-',
                            $record->monthly_quota ?? 0,
                            number_format($record->package_price, 2),
                            $record->package_discount ? $record->package_discount : '0%'
                        );
                    })
                    ->wrap(),

                TextColumn::make('unit_used')->numeric(),
                TextColumn::make('excess_unit_used')->numeric(),
                TextColumn::make('excess_per_unit_price')->numeric(),
                TextColumn::make('excess_unit_cost')->numeric(),
                TextColumn::make('total_cost')->numeric(),
            ])
            ->filters([
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
                            ->options(fn () => array_combine(
                                range(now()->year, now()->year - 5),
                                range(now()->year, now()->year - 5)
                            )),

                        auth()->user()->hasAnyRole(['super_admin', 'support',])
                            ? Select::make('client_id')
                                ->label('Client')
                                ->options(\App\Models\Client::pluck('name', 'id')->toArray())
                                ->placeholder('Select a Client')
                            : null,
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['month'])) {
                            $query->where('month', $data['month']);
                        }
                        if (!empty($data['year'])) {
                            $query->where('year', $data['year']);
                        }
                        if (!empty($data['client_id'])) {
                            $query->where('client_id', $data['client_id']);
                        }
                    })
                    ->default([
                        'month' => now()->month,
                        'year'  => now()->year,
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($record) => redirect()->route(
                        'reports.tickets.download',
                        [
                            'client' => $record->client_id,
                            'year' => $record->year,
                            'month' => $record->month,
                        ]
                    )),
            ])

            ->headerActions([
                Action::make('downloadReport')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->disabled(function ($livewire): bool {
                        $user = auth()->user();

                        // If admin → require client, year and month
                        if ($user->client_id === null) {
                            return empty($livewire->tableFilters['month']['client_id'])
                                || empty($livewire->tableFilters['month']['year'])
                                || empty($livewire->tableFilters['month']['month']);
                        }

                        // If client user → require only year and month
                        return empty($livewire->tableFilters['month']['year'])
                            || empty($livewire->tableFilters['month']['month']);
                    })
                    ->action(function (Action $action, $livewire) {
                        $month = Arr::get($livewire->tableFilters, 'month.month', now()->month);
                        $year = Arr::get($livewire->tableFilters, 'month.year', now()->year);

                        $clientId = auth()->user()->client_id ?: Arr::get($livewire->tableFilters, 'month.client_id');

                        if (empty($clientId)) {
                            Notification::make()
                                ->title('Client Not Selected')
                                ->body('Please select a client from the filters to generate a report.')
                                ->danger()
                                ->send();
                            $action->halt();
                            return;
                        }

                        return redirect()->route('reports.tickets.download', [
                            'client' => $clientId,
                            'year' => $year,
                            'month' => $month,
                        ]);
                    })
                    ->requiresConfirmation(false),
                Action::make('downloadPreviousMonthReports')
                    ->label('Download Previous Month Reports')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn() => auth()->user()->hasAnyRole(['super_admin', 'support']))
                    ->action(function () {
                        // Call your controller method
                        return app(\App\Http\Controllers\TicketReportController::class)
                            ->downloadAllPreviousMonth();
                    })
                    ->requiresConfirmation(false),
            ]);
    }
}
