<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use Carbon\Carbon;

class ClientDashboard extends StatsOverviewWidget
{

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->client()->exists();
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $client = $user->client;
        
        if(!$client){
                return [
                Stat::make('Total Tickets', 0)->description('Client not found')->color('gray'),
            ];
        }

        $now = Carbon::now('Asia/Dhaka');
        $monthName = $now->format('F Y');

        $monthlyTickets = Ticket::query()
        ->where('client_id', $client->id)
        ->whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->get();

        $total_tickets = $monthlyTickets->count();
        $extra_units = $monthlyTickets->where('is_excess_unit', true)->sum('unit_value');

        $used_units = $monthlyTickets->sum('unit_value') - $extra_units;
        

        $package = $client->package;
        $packageName = $package->name ?? 'N/A';
        $packageLimit = $package->monthly_quota ?? 0;

        $base_price = $package->base_price;
        $package_discount = $client->discount;

        $remainingUnits = max($packageLimit - $used_units, 0);
        $total_monthly_bill = $package->base_price + $extra_units * $package->excess_unit_price - $client->discount;


        return [
            
            Stat::make("Package for {$monthName}", $packageName)
                ->description("Base Price: {$base_price}")
                ->color('info'),
            
            Stat::make('Total Tickets', $total_tickets)
                ->description("For {$monthName}")
                ->color('primary'),

            Stat::make('Used Units', $used_units)
                ->description("Out of {$packageLimit}")
                ->color('warning'),

            Stat::make('Remaining Units', $remainingUnits)
                ->description("Out of {$packageLimit}")
                ->color($remainingUnits > 0 ? 'success' : 'danger'),

            Stat::make('Extra Units', $extra_units)
                ->description("For this month"),
                
            Stat::make('Monthly bill', $total_monthly_bill)
                ->description("For {$monthName} [ Pckage price + extra unit price - discount]"),    

            ];
        
    }
}
