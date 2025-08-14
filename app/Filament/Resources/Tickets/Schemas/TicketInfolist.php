<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\HtmlString;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
       return $schema
                ->columns(1)
                ->schema([
                    // Main Info + Timestamps in two columns
                    Section::make(fn ($record) => "{$record->subject} #{$record->id} [" . ucfirst($record->status->value) . "]")
                        ->schema([
                            TextEntry::make('description')
                                ->hiddenLabel()
                                ->formatStateUsing(fn (string $state): HtmlString => new HtmlString($state)),

                            TextEntry::make('user.name')
                                ->hiddenLabel()
                                ->formatStateUsing(fn ($record) =>
                                    "Created by {$record->user->name} ({$record->client->name}) - {$record->created_at->diffForHumans()}"
                            ),
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
