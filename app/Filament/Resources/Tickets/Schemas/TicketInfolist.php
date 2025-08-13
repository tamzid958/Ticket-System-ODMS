<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Filament\Schemas\Components\Grid;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
       return $schema
    ->columns(1)
    ->schema([
        // Main Info + Timestamps in two columns
        Section::make('Ticket Details')
            ->columns(2) // Two columns inside section
            ->schema([
                // Left Column: Main Info
                TextEntry::make('user.name')
                    ->label('User Name'),
                TextEntry::make('client.name')
                    ->label('Client Name'),
                TextEntry::make('subject')
                    ->label('Subject'),
                TextEntry::make('status')
                    ->label('Status'),

                // Right Column: Timestamps
                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
                TextEntry::make('resolved_at')
                    ->label('Resolved At')
                    ->dateTime(),
            ])
            ->columnSpanFull(), // adds space between fields

        // Comments Section
        Section::make('Comments')
            ->schema([
                CommentsEntry::make('comments')
                    ->mentionables(fn ($record) => User::all())
                    ->perPage(8)
                    ->loadMoreIncrementsBy(8)
                    ->loadMoreLabel('Show older'),
            ])
            ->columnSpanFull()
            ,
    ]);
    }
}
