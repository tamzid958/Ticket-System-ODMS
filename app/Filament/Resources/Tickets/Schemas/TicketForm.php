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

    // This role-aware logic is correct and necessary for the dynamic updates.
    protected static function resolveClientId($get): ?int
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin', 'support'])) {
            return $get('client_id');
        }

        return $user->client_id;
    }

    public static function calculateUnitValue($get): ?float
    {
        $ticketWeight = $get('ticket_weight') ?? 1;
        $clientId = self::resolveClientId($get);

        if (!$clientId) {
            return null;
        }

        $service = self::resolveService($clientId);
        if (!$service) {
            return null;
        }

        $createdAt = $get('created_at') ?? now();
        $message = $service->getUnitMessage($ticketWeight, Carbon::parse($createdAt), $clientId);
        return $service->extractTotalUnitsFromUnitMessage($message);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // ... subject and description fields are unchanged ...
            TextInput::make('subject')->required()->maxLength(255)->columnSpanFull(),
            RichEditor::make('description')->required()->columnSpanFull()->extraAttributes(['style' => 'min-height: 300px;']),

            // Admin-only fields with their `afterStateUpdated` hooks are correct
            DateTimePicker::make('created_at')->label('Created At')->default(now())->seconds(false)->live()
                ->visible(fn() => Auth::user()->hasRole('super_admin'))
                ->afterStateUpdated(function ($set, $get) {
                    $set('unit_value', self::calculateUnitValue($get));
                })->columnSpanFull(),

            TextInput::make('ticket_weight')->default(1)->numeric()->live()
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                ->afterStateUpdated(function ($set, $get) {
                    $set('unit_value', self::calculateUnitValue($get));
                })->columnSpanFull(),

            Select::make('client_id')->label('Client')->options(fn() => Client::pluck('name', 'id'))->live()
                ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                ->required(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                ->afterStateUpdated(function ($set, $get) {
                    $set('unit_value', self::calculateUnitValue($get));
                })->columnSpanFull(),


            TextInput::make('unit_value')
                ->label('Calculated Unit Value')
                ->disabled()
                ->dehydrated(false)
                ->default(function ($get) {
                    $user = Auth::user();
                    if ($user->hasRole('client') && $user->client_id) {
                        return self::calculateUnitValue($get) ?? 0;
                    }
                    return null; // For admins, starts as null until they select things
                })
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