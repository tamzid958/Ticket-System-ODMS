<?php

namespace App\Filament\Resources\Tickets\Schemas;

use App\Services\TicketUnitService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use App\Models\Client;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;

class TicketForm
{
    protected static function resolveService($clientId): ?TicketUnitService
    {
        $client = Client::find($clientId)->versionedInstance();
        $package = $client?->package->versionedInstance();
        return $package ? new TicketUnitService($package) : null;
    }

    protected static function resolveClientId($get): ?int
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin', 'support',])) {
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
            TextInput::make('subject')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            RichEditor::make('description')
                ->disableAllToolbarButtons()
                ->required()
                ->columnSpanFull()
                ->extraAttributes(['style' => 'min-height: 300px;']),
            FileUpload::make('attachments')
                ->label('attachments')
                ->acceptedFileTypes([
                    // Images
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/bmp',
                    'image/webp',
                    'image/svg+xml',

                    // Documents
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv',
                    'text/plain',
                    'text/markdown',

                    // Archives
                    'application/zip',
                    'application/x-rar-compressed',
                    'application/x-7z-compressed',

                    // Videos
                    'video/mp4',
                    'video/quicktime',
                    'video/x-msvideo',
                    'video/x-matroska',
                ])
                ->maxSize(5120)
                ->disk('public')
                ->directory('ticket-attachments/temp')
                ->visibility('public')
                ->multiple()
                ->required(false)
                ->rules([
                    'mimetypes:' . implode(',', [
                        // Images
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'image/bmp',
                        'image/webp',
                        'image/svg+xml',

                        // Documents
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                        'text/plain',
                        'text/markdown',

                        // Archives
                        'application/zip',
                        'application/x-rar-compressed',
                        'application/x-7z-compressed',

                        // Videos
                        'video/mp4',
                        'video/quicktime',
                        'video/x-msvideo',
                        'video/x-matroska',
                    ]),
                    'max:5120',
                ])
                ->preserveFilenames()
                ->afterStateUpdated(function ($state, $set, $get) {
                    // Optional client-side updates
                })
                ->columnSpanFull(),

            DateTimePicker::make('created_at')
                ->label('Created At')
                ->default(Carbon::now('Asia/Dhaka'))
                ->seconds(false)
                ->live()
                ->timezone('Asia/Dhaka')
                ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'support',]))
                ->afterStateUpdated(function ($set, $get) {
                    $set('unit_value', self::calculateUnitValue($get));
                })
                ->columnSpanFull(),

            TextInput::make('ticket_weight')->default(1)->numeric()->live()
                ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'support',]))
                ->afterStateUpdated(function ($set, $get) {
                    $set('unit_value', self::calculateUnitValue($get));
                })->columnSpanFull(),

            Select::make('client_id')->label('Client')->options(fn() => Client::pluck('name', 'id'))->live()
                ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'support',]))
                ->required(fn() => Auth::user()->hasAnyRole(['super_admin', 'support',]))
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
                    return null;
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
