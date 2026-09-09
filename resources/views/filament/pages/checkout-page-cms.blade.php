<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Checkout page content</x-slot>
        <x-slot name="description">
            Only informational copy is editable here. Prices, payment methods,
            validation, order creation and security stay fully application-controlled.
            Changes appear on both checkout pages immediately after saving.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save checkout content
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
