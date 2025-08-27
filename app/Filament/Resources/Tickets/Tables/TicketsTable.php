<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Ticket;
use Filament\Actions\Action;
use App\Enums\TicketStatus;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
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
                    ->latest();
            })
            ->columns(array_filter([
                TextColumn::make('user.name')->sortable(),
                auth()->user()->client_id ? null : TextColumn::make('client.name')->sortable(),
                TextColumn::make('subject')->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('resolved_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('unit_value')
                    ->label('Unit Value')
                    ->formatStateUsing(function ($record) {
                        $message = $record->getUnitMessage();
                        return $record->unit_value 
                            . ' '
                            . '<span title="' . e($message) . '" style="display:inline-block; margin-left:0.5rem; color:#9CA3AF; font-size:1.2em; ">&#9432;</span>';
                    })
                    ->html()
                    ->sortable(),
                TextColumn::make('is_excess_unit')
                    ->label('Excess Unit')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->sortable(),
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
            ])

            ->recordActions([
                ViewAction::make(),
                Action::make('resolve')
                    ->label('Resolve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== TicketStatus::CLOSED)
                    ->action(function ($record) {
                        $record->update([
                            'status' => TicketStatus::CLOSED,
                            'resolved_at' => now(),
                        ]);
                    }),
            ]);
    }
}
