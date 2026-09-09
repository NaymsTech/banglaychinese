<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Send an email</x-slot>
        <x-slot name="description">
            Pick a template, choose who receives it, fill in the placeholders,
            then review the summary. Use the “Send Emails” button at the top
            right to confirm and send — emails go out synchronously through the
            highest-priority active provider and are recorded in the email log.
        </x-slot>

        {{ $this->form }}
    </x-filament::section>
</x-filament-panels::page>
