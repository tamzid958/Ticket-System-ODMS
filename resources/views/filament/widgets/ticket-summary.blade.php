<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold mb-2">Monthly Ticket Summary</h2>

        <div class="grid grid-cols-4 gap-4">
            <div class="text-center">
                <span class="block text-2xl font-bold">{{ $totalTickets }}</span>
                <span>Total Tickets</span>
            </div>
                        <div class="text-center">
                <span class="block text-2xl font-bold">{{ $pendingTickets }}</span>
                <span>Pending Tickets</span>
            </div>
            <div class="text-center">
                <span class="block text-2xl font-bold">{{ $usedTickets }}</span>
                <span>Tickets Used</span>
            </div>
            <div class="text-center">
                <span class="block text-2xl font-bold">{{ $leftTickets }}</span>
                <span>Tickets Left</span>
            </div>

        </div>
    </x-filament::card>
</x-filament::widget>
