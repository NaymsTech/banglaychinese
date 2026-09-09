<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Privacy Policy content</x-slot>
        <x-slot name="description">
            Edit the Privacy Policy page copy. Changes appear on the public page
            ({{ route('pages.show', 'privacy-policy') }}) immediately after saving.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save privacy policy
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
