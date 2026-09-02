<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Site settings</x-slot>
        <x-slot name="description">
            Values saved here are used across the public website (contact links,
            WhatsApp buttons, analytics snippets, payment numbers, etc.).
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save settings
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
