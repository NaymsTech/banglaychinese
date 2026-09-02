<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">About page content</x-slot>
        <x-slot name="description">
            Expand a group below to edit its content. JSON fields are validated on save.
            Image and JSON-based sections are edited here; labels and order are stored too.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save about page
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
