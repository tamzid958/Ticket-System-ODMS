<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;
use App\Models\User;
use Carbon\Carbon;
use Filament\Infolists\Components\Html;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\Facades\Storage;


class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                // Main Info + Timestamps
                Section::make(fn($record) => "{$record->subject} #{$record->id} [" . ucfirst($record->status->value) . "]")
                    ->schema([
                        TextEntry::make('description')
                        ->hiddenLabel()
                        ->html()
                        ->formatStateUsing(function (?string $state): string {
                            if (!$state) return '';

                            $state = preg_replace_callback(
                                '/src="([^"]+)"/i',
                                function ($matches) {
                                    $src = $matches[1];

                                    if (str_starts_with($src, 'http')) {
                                        return 'src="' . $src . '"';
                                    }

                                    if (str_starts_with($src, 'storage')) {
                                        return 'src="' . asset($src) . '"';
                                    }

                                    return 'src="' . asset('storage/' . ltrim($src, '/')) . '"';
                                },
                                $state
                            );

                            return $state;
                        }),
                        TextEntry::make('user.name')
                            ->hiddenLabel()
                            ->formatStateUsing(function ($state, $record) {
                                $clientName = $record->client?->name ?? 'N/A';
                                $createdAt = $record->created_at ? $record->created_at->diffForHumans() : '-';
                                // $state is user name
                                if (auth()->user()->hasRole('client')) {
                                    return "Created by {$state}  - {$createdAt}";

                                }
                

                                return "Created by {$state} ({$clientName}) - {$createdAt}";
                            }),

                    ])
                    ->columnSpanFull(),

                Section::make('Attachments')
                ->schema([
                    ViewEntry::make('attachments')
                        ->view('filament.components.file-links')
                        ->viewData(function ($record) {
                            $directory = "ticket-attachments/{$record->id}";
                            
                            if (!Storage::disk('public')->exists($directory)) {
                                return ['files' => []];
                            }
                            
                            $files = Storage::disk('public')->files($directory);
                            $fileData = [];
                            
                            foreach ($files as $file) {
                                $fileData[] = [
                                    'name' => basename($file),
                                    'url' => asset('storage/' . $file),
                                    'size' => Storage::disk('public')->size($file),
                                ];
                            }
                            
                            return ['files' => $fileData];
                        })
                ])
                ->columnSpanFull(),


                // Resolution Details (only if ticket is closed)
                Section::make('Resolution Details')
                    ->visible(fn($record) => $record->status->value === 'closed')
                    ->schema([
                        TextEntry::make('resolved_at')
                            ->label('Resolved At')
                            ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->format('h:i A, Y-m-d') : '-'),

                        TextEntry::make('resolver.name')
                            ->label('Resolved By')
                            ->formatStateUsing(fn($state) => $state ?: '-'),

                    ])
                    ->columnSpanFull(),

                // Comments Section
                Section::make('Comments')
                    ->schema([
                        CommentsEntry::make('comments')
                            ->mentionables(fn($record) => User::all())
                            ->perPage(8)
                            ->loadMoreIncrementsBy(8)
                            ->loadMoreLabel('Show older'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
