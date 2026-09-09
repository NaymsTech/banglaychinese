<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Refund & Returns Policy content</x-slot>
        <x-slot name="description">
            Edit the Refund & Returns Policy page copy. Changes appear on the public
            page ({{ route('pages.show', 'refund-and-returns-policy') }}) immediately after saving.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save refund policy
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
