<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\ManageClients;
use App\Models\Client;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
use Filament\Tables\Columns\ToggleColumn;


class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Star;

    protected static ?string $recordTitleAttribute = 'Client';

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                Select::make('package_id')
                    ->label('Package')
                    ->relationship('package', 'name')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('discount')
                    ->label('Discount')
                    ->numeric()
                    ->default(0)
                    ->dehydrateStateUsing(fn ($state) => $state === null ? 0 : $state)
                    ->rules(function (callable $get, $component = null) {
                        return [
                            function (string $attribute, $value, \Closure $fail) use ($get, $component) {
                                $label = $component?->getLabel() ?? $attribute;
                                $value = $value ?? 0;

                                if ($value < 0) {
                                    $fail("The {$label} cannot be less than 0.");
                                    return;
                                }

                                $packageId = $get('package_id');
                                if ($packageId) {
                                    $package = \App\Models\Package::find($packageId);
                                    if ($package && $value > $package->base_price) {
                                        $fail("The {$label} cannot be greater than the base price ({$package->base_price}).");
                                    }
                                }
                            },
                        ];
                    })
                    ->columnSpanFull(),


                Toggle::make('will_generate_report')
                    ->label('Generate Report Automatically')
                    ->default(true)
                    ->columnSpanFull(),
                
                Select::make('users')
                    ->multiple()
                    ->label('Users')
                    ->relationship('users', 'name', fn ($query) => $query->role('client'))
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordTitleAttribute('Client')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('package.name'),
                TextColumn::make('discount')
                    ->label('Discount')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->extraAttributes(['class' => 'text-right']),
    
                ToggleColumn::make('will_generate_report')
                    ->label('Generate Report Automatically')
                    ->sortable(),
    
                TextColumn::make('users_display')
                    ->label('Users')
                    ->getStateUsing(function ($record) {
                        $users = $record->relationLoaded('users') ? $record->users : $record->users()->get();
    
                        $uniqueUsers = $users->unique('id');
                        $userNames = $uniqueUsers->pluck('name');
    
                        if ($userNames->isEmpty()) {
                            return 'No users assigned';
                        }
    
                        if ($userNames->count() <= 3) {
                            return $userNames->join(', ');
                        }
    
                        return $userNames->take(3)->join(', ') . ' and ' . ($userNames->count() - 3) . ' more...';
                    })
                    ->tooltip(function ($record) {
                        $users = $record->relationLoaded('users') ? $record->users : $record->users()->get();
                        $uniqueUsers = $users->unique('id');
                        return $uniqueUsers->pluck('name')->join(', ');
                    })
                    ->wrap(),
    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => ManageClients::route('/'),
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
