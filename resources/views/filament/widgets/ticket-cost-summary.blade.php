<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-bold mb-2">Monthly Ticket Cost</h2>

        <div class="grid grid-cols-1 gap-2">
            <div class="flex justify-between">
                <span>Base Price:</span>
                <span>${{ number_format($basePrice, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Extra Ticket Price:</span>
                <span>${{ number_format($extraPrice, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold">
                <span>Total:</span>
                <span>${{ number_format($totalPrice, 2) }}</span>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
