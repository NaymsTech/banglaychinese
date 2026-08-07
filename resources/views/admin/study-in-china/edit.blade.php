@extends('layouts.admin')

@section('page-title', 'Study in China — Page Editor')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Study in China — Page Editor</h1>
            <p class="mt-1 text-sm text-slate-500">Manage all content sections of the Study in China landing page.</p>
        </div>
        <a href="{{ route('study-in-china.index') }}" target="_blank"
           class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            View Page
        </a>
    </div>

    <div x-data="{ activeTab: 'hero' }" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto border-b border-slate-200">
            <nav class="flex space-x-1 px-4 min-w-max">
                @foreach($sections as $group => $fields)
                    <button @click="activeTab = '{{ $group }}'"
                            :class="activeTab === '{{ $group }}' ? 'border-red-500 text-red-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                            class="border-b-2 px-4 py-3 text-sm font-semibold transition-colors whitespace-nowrap">
                        {{ $groupLabels[$group] ?? ucwords(str_replace('_', ' ', $group)) }}
                    </button>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            @foreach($sections as $group => $fields)
                <div x-show="activeTab === '{{ $group }}'" x-cloak>
                    <h3 class="mb-6 text-lg font-bold text-slate-800">
                        {{ $groupLabels[$group] ?? ucwords(str_replace('_', ' ', $group)) }}
                    </h3>

                    <div class="space-y-6">
                        @foreach($fields as $section)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                                <div class="mb-3 flex items-start justify-between">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">
                                            {{ $section->label }}
                                        </label>
                                        <code class="mt-0.5 block text-xs text-slate-400">{{ $section->key }}</code>
                                    </div>
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold
                                        {{ $section->type === 'image' ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $section->type === 'json' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $section->type === 'longtext' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $section->type === 'text' ? 'bg-green-100 text-green-700' : '' }}">
                                        {{ $section->type }}
                                    </span>
                                </div>

                                @if($section->type === 'text')
                                    <div class="flex gap-2">
                                        <input type="text"
                                               value="{{ $section->value }}"
                                               data-key="{{ $section->key }}"
                                               class="cms-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-1 focus:ring-red-200"
                                               placeholder="Enter text...">
                                        <button onclick="saveField(this)"
                                                class="flex-shrink-0 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                            Save
                                        </button>
                                    </div>
                                    <span class="save-status ml-2 hidden text-xs text-green-600">✓ Saved</span>

                                @elseif($section->type === 'longtext')
                                    <div class="flex gap-2">
                                        <textarea rows="4"
                                                  data-key="{{ $section->key }}"
                                                  class="cms-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-1 focus:ring-red-200 resize-none"
                                                  placeholder="Enter text...">{{ $section->value }}</textarea>
                                        <button onclick="saveField(this)"
                                                class="h-fit flex-shrink-0 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                            Save
                                        </button>
                                    </div>
                                    <span class="save-status ml-2 hidden text-xs text-green-600">✓ Saved</span>

                                @elseif($section->type === 'image')
                                    <div class="space-y-3">
                                        @if($section->value)
                                            <img src="{{ asset($section->value) }}"
                                                 alt="{{ $section->label }}"
                                                 class="h-32 w-32 rounded-lg border object-cover">
                                        @endif
                                        <div class="flex gap-2">
                                            <input type="text"
                                                   value="{{ $section->value }}"
                                                   data-key="{{ $section->key }}"
                                                   class="cms-field flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-1 focus:ring-red-200"
                                                   placeholder="Image path">
                                            <button onclick="saveField(this)"
                                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                                Save
                                            </button>
                                        </div>
                                        <span class="save-status ml-2 hidden text-xs text-green-600">✓ Saved</span>
                                    </div>

                                @elseif($section->type === 'json')
                                    <div class="space-y-2">
                                        <textarea rows="{{ max(6, substr_count($section->value, "\n") + 1) }}"
                                                  data-key="{{ $section->key }}"
                                                  data-type="json"
                                                  class="cms-field w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm outline-none focus:border-red-500 focus:ring-1 focus:ring-red-200"
                                                  placeholder="JSON content...">{{ $section->value }}</textarea>
                                        <div class="flex items-center gap-2">
                                            <button onclick="saveJsonField(this)"
                                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                                Save JSON
                                            </button>
                                            <span class="save-status hidden text-xs text-green-600">✓ Saved</span>
                                            <button onclick="formatJson(this)"
                                                    class="rounded-lg px-3 py-2 text-sm text-slate-600 hover:text-slate-900 transition-colors">
                                                Format
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function saveField(btn) {
        const container = btn.closest('.space-y-6 > div') || btn.parentElement.parentElement;
        const input = container.querySelector('.cms-field');
        const status = container.querySelector('.save-status');
        const key = input.dataset.key;
        const value = input.value;

        btn.disabled = true;
        status?.classList.add('hidden');

        fetch('{{ route("admin.study-in-china.update-by-key") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ key, value }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                status?.classList.remove('hidden');
                setTimeout(() => status?.classList.add('hidden'), 3000);
            }
        })
        .catch(err => alert('Error saving. Please try again.'))
        .finally(() => btn.disabled = false);
    }

    function saveJsonField(btn) {
        const container = btn.closest('.space-y-6 > div') || btn.parentElement.parentElement;
        const textarea = container.querySelector('.cms-field');
        const status = container.querySelector('.save-status');
        const key = textarea.dataset.key;
        let data;

        try {
            data = JSON.parse(textarea.value);
        } catch (e) {
            alert('Invalid JSON. Please check your syntax.');
            return;
        }

        btn.disabled = true;
        status?.classList.add('hidden');

        fetch('{{ route("admin.study-in-china.update-json") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ key, data }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                status?.classList.remove('hidden');
                setTimeout(() => status?.classList.add('hidden'), 3000);
            }
        })
        .catch(err => alert('Error saving. Please try again.'))
        .finally(() => btn.disabled = false);
    }

    function formatJson(btn) {
        const container = btn.closest('.space-y-6 > div') || btn.parentElement.parentElement;
        const textarea = container.querySelector('.cms-field');
        try {
            const parsed = JSON.parse(textarea.value);
            textarea.value = JSON.stringify(parsed, null, 2);
        } catch (e) {
            alert('Invalid JSON. Cannot format.');
        }
    }
</script>
@endpush
