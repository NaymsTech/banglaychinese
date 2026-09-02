<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Study in China page content</x-slot>
        <x-slot name="description">
            Expand a group below to edit its content. JSON fields are validated on save.
            The legacy 'services' group is managed under Services, not here.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save page content
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
