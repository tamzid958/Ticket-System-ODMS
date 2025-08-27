<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Services\TicketUnitService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use App\Models\Client;
use App\Models\Package;

class TicketForm
{
    protected static function resolveService($clientId): ?TicketUnitService
    {
        $client = Client::find($clientId);
        $package = $client?->package ?? Package::first();
        return $package ? new TicketUnitService($package) : null;
    }

    protected static function resolveClientId($get): ?int
    {
        return $get('client_id') ?: Auth::user()->client_id ?: Client::first()?->id;
    }

    protected static function calculateUnitValue($get): ?float
    {
        $ticketWeight = $get('ticket_weight') ?? 1;
        $clientId = self::resolveClientId($get);
        if (!$clientId)
            return null;

        $service = self::resolveService($clientId);
        if (!$service)
            return null;

        // Use selected datetime
        $createdAt = $get('created_at') ?? now();
        $message = $service->getUnitMessage($ticketWeight, Carbon::parse($createdAt), $clientId);
        return $service->extractTotalUnitsFromUnitMessage($message);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Subject
            TextInput::make('subject')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            // Description
            RichEditor::make('description')
                ->required()
                ->columnSpanFull()
                ->extraAttributes(['style' => 'min-height: 300px;']),
            
            DateTimePicker::make('created_at')
                ->label('Created At')
                ->default(now())
                ->seconds(false)
                ->live() // Changed from reactive() to live()
                ->visible(fn() => Auth::user()->hasRole('super_admin'))
                ->afterStateUpdated(function ($state, $set, $get) {
                    // Recalculate unit value when created_at changes
                    $unitValue = self::calculateUnitValue($get);
                    $set('unit_value', $unitValue);
                })
                ->columnSpanFull(),

            // Ticket Weight (editable only by super_admin)
            TextInput::make('ticket_weight')
                ->default(1)
                ->numeric()
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                ->live() // Changed from reactive() to live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    // Recalculate unit value when ticket weight changes
                    $unitValue = self::calculateUnitValue($get);
                    $set('unit_value', $unitValue);
                })
                ->columnSpanFull(),

            // Client select (only for super_admin and support)
            Select::make('client_id')
                ->label('Client')
                ->options(fn() => Client::pluck('name', 'id'))
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                ->required(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                ->live() // Changed from reactive() to live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    // Recalculate unit value when client changes
                    $unitValue = self::calculateUnitValue($get);
                    $set('unit_value', $unitValue);
                })
                ->columnSpanFull(),

            // Calculated Unit Value
            TextInput::make('unit_value')
                ->label('Calculated Unit Value')
                ->state(function ($get) {
                    // Use state() instead of default() for dynamic updates
                    return self::calculateUnitValue($get);
                })
                ->disabled()
                ->dehydrated(false) // Don't include in form data since it's calculated
                ->hintIcon('heroicon-o-information-circle')
                ->hintIconTooltip(function ($get) {
                    $ticketWeight = $get('ticket_weight') ?? 1;
                    $clientId = self::resolveClientId($get);
                    if (!$clientId)
                        return 'Please select a client to see unit calculation.';

                    $service = self::resolveService($clientId);
                    if (!$service)
                        return 'Client or package not found.';

                    $createdAt = $get('created_at') ?? now();
                    return $service->getUnitMessage($ticketWeight, Carbon::parse($createdAt), $clientId);
                })
                ->columnSpanFull(),
        ]);
    }
}