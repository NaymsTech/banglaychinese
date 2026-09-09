<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">FAQ content</x-slot>
        <x-slot name="description">
            Edit the FAQ questions and answers. Changes appear on the public page
            ({{ route('pages.show', 'faq') }}) immediately after saving.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save FAQ
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
