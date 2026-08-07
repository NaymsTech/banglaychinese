@php
    $value = old($section->key, $section->value);
@endphp

<div class="{{ $section->type === 'text' ? 'md:col-span-2' : '' }}">
    <label for="{{ $section->key }}" class="block mb-1 text-sm font-medium text-slate-700">
        {{ $section->label ?? ucwords(str_replace('_', ' ', $section->key)) }}
        <span class="text-xs text-slate-400 ml-1">({{ $section->type }})</span>
    </label>

    @if($section->type === 'text')
        <textarea
            name="{{ $section->key }}"
            id="{{ $section->key }}"
            rows="6"
            class="w-full rounded-lg bg-white border border-slate-300 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
        >{{ $value }}</textarea>

    @elseif($section->type === 'json')
        <textarea
            name="{{ $section->key }}"
            id="{{ $section->key }}"
            rows="8"
            class="w-full rounded-lg bg-slate-50 border border-slate-300 px-4 py-2.5 text-sm text-slate-800 font-mono placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
        >{{ $value }}</textarea>
        <p class="mt-1 text-xs text-slate-400">Valid JSON array of objects. Edit carefully.</p>

    @elseif($section->type === 'image')
        @if($value)
            <div class="mb-2">
                <img src="{{ asset('storage/' . $value) }}" alt="{{ $section->label }}" class="h-24 rounded-lg border border-slate-200 object-cover">
            </div>
        @endif
        <input
            type="file"
            name="{{ $section->key }}"
            id="{{ $section->key }}"
            class="w-full rounded-lg bg-white border border-slate-300 px-4 py-2.5 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-600 file:px-3 file:py-1 file:text-sm file:text-white hover:file:bg-primary-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
        >

    @else
        <input
            type="text"
            name="{{ $section->key }}"
            id="{{ $section->key }}"
            value="{{ $value }}"
            class="w-full rounded-lg bg-white border border-slate-300 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
        >
    @endif
</div>
