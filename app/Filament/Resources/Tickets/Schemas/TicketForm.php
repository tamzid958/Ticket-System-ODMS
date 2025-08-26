<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Services\TicketUnitService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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

                // Ticket Weight (editable only by super_admin)
                TextInput::make('ticket_weight')
                    ->default(1)
                    ->numeric()
                    ->disabled(fn() => !Auth::user()->hasRole('super_admin'))
                    ->reactive() // Make it reactive to trigger unit calculation updates
                    ->columnSpanFull(),

                // Client select (only for super_admin and support)
                Select::make('client_id')
                    ->label('Client')
                    ->options(function () {
                        return \App\Models\Client::pluck('name', 'id');
                    })
                    ->visible(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                    ->required(fn() => Auth::user()->hasAnyRole(['super_admin', 'support']))
                    ->reactive() // Make it reactive to trigger unit calculation updates
                    ->columnSpanFull(),

                // Placeholder to show calculated units and price info
                Placeholder::make('unit_info')
                    ->content(function ($get) {
                        $ticketWeight = $get('ticket_weight') ?? 1;
                        $clientId = null;
                        
                        // Get client ID based on user role
                        if (Auth::user()->hasAnyRole(['super_admin', 'support'])) {
                            $clientId = $get('client_id');
                        } else {
                            $clientId = Auth::user()->client_id;
                        }
                        
                        if (!$clientId) {
                            return 'Please select a client to see unit calculation.';
                        }
                        
                        // Get client's package
                        $client = \App\Models\Client::find($clientId);
                        $package = $client->package ?? \App\Models\Package::first();
                        
                        $service = new TicketUnitService($package);
                        return $service->getUnitMessage(
                            $ticketWeight,
                            Carbon::now('Asia/Dhaka'),
                            $clientId
                        );
                    })
                    ->columnSpanFull(),

                // Hidden fields to store calculated values (optional, for debugging)
                TextInput::make('unit_value')
                    ->label('Calculated Unit Value')
                    ->disabled()
                    ->visible(fn() => Auth::user()->hasRole('super_admin'))
                    ->columnSpan(1),

                TextInput::make('unit_discount')
                    ->label('Unit Discount')
                    ->disabled()
                    ->visible(fn() => Auth::user()->hasRole('super_admin'))
                    ->columnSpan(1),
            ]);
    }
}