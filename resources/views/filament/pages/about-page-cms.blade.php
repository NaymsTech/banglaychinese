<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">About page content</x-slot>
        <x-slot name="description">
            Edit the About page copy section by section, in the same order as the
            public page. Lists are managed as repeatable rows; long text supports
            rich formatting. Changes appear on the public page immediately after saving.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save about page
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
