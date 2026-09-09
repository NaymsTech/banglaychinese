<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Home page content</x-slot>
        <x-slot name="description">
            Edit the homepage copy section by section, in the same order as the
            public page. Changes appear on the homepage immediately after saving.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save home page
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
