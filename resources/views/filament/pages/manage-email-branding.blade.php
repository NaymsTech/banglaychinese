@php
    // Rendered server-side through the canonical delivery seam
    // (EmailService::renderForDelivery -> EmailShell -> emails.layouts.branded)
    // using the current — possibly unsaved — form state. The iframe is keyed
    // by the document hash so Livewire replaces it whenever the preview
    // content actually changes, guaranteeing the new HTML is displayed.
    $previewDocument = $this->renderPreview();
    $previewKey = 'email-branding-preview-'.substr(md5($previewDocument), 0, 12);
@endphp
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Email branding</x-slot>
        <x-slot name="description">
            One configuration controls the visual layer of every branded email
            (the 11 database templates plus the authentication emails). Template
            bodies only contain email-specific content — the logo, tagline,
            colors, footer and contact details below are applied globally by the
            email shell. Changes apply to the next email that is sent.
        </x-slot>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-check">
                Save email branding
            </x-filament::button>
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Live preview</x-slot>
        <x-slot name="description">
            This is the exact branded email document that delivery produces —
            rendered by EmailService → EmailShell with the values above. Changes
            appear live as you type (no need to save first); “Save email
            branding” persists them for real recipients.
        </x-slot>

        <div class="flex w-full justify-center">
            <div class="w-full" style="max-width:680px;">
                <iframe
                    title="Branded email preview"
                    wire:key="{{ $previewKey }}"
                    srcdoc="{{ $previewDocument }}"
                    style="width:100%;height:780px;border:1px solid #cbd5e1;border-radius:0.75rem;background:#fff;box-shadow:0 6px 24px rgba(16,24,40,0.08);"
                ></iframe>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
