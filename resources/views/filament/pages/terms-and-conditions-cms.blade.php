<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Terms & Conditions content</x-slot>
        <x-slot name="description">
            Edit the Terms & Conditions page copy. Changes appear on the public page
            ({{ route('pages.show', 'terms-and-conditions') }}) immediately after saving.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save terms & conditions
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
