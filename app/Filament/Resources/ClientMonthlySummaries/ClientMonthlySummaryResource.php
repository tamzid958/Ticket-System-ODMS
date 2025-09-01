<?php

namespace App\Filament\Resources\ClientMonthlySummaries;

use App\Filament\Resources\ClientMonthlySummaries\Pages\CreateClientMonthlySummary;
use App\Filament\Resources\ClientMonthlySummaries\Pages\EditClientMonthlySummary;
use App\Filament\Resources\ClientMonthlySummaries\Pages\ListClientMonthlySummaries;
use App\Filament\Resources\ClientMonthlySummaries\Pages\ViewClientMonthlySummary;
use App\Filament\Resources\ClientMonthlySummaries\Schemas\ClientMonthlySummaryForm;
use App\Filament\Resources\ClientMonthlySummaries\Schemas\ClientMonthlySummaryInfolist;
use App\Filament\Resources\ClientMonthlySummaries\Tables\ClientMonthlySummariesTable;
use App\Models\ClientMonthlySummary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientMonthlySummaryResource extends Resource
{
    protected static ?string $model = ClientMonthlySummary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Newspaper;

    protected static ?string $recordTitleAttribute = 'MonthlySummary';

    protected static ?string $title = "Report";

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $modelLabel = 'Report';
    
    protected static ?string $pluralModelLabel = 'Reports';

    public static function form(Schema $schema): Schema
    {
        return ClientMonthlySummaryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientMonthlySummaryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientMonthlySummariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientMonthlySummaries::route('/')
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
