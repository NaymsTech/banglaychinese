@props(['type' => 'success'])

@php
    $styles = [
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
    ];
    $icons = [
        'success' => '<svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        'error' => '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
    ];
@endphp

@if ($type === 'success' && session('success'))
    <div class="mb-6 p-4 border rounded-lg flex items-start gap-3 {{ $styles['success'] }}">
        {!! $icons['success'] !!}
        <p>{{ session('success') }}</p>
    </div>
@endif

@if ($type === 'error' && $errors->any())
    <div class="mb-6 p-4 border rounded-lg flex items-start gap-3 {{ $styles['error'] }}">
        {!! $icons['error'] !!}
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
