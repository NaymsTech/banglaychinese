<div x-data="{ features: {{ isset($service) ? json_encode($service->features ?? []) : '[]' }} }">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $service->name ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   required maxlength="255">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label for="slug" class="block text-sm font-semibold text-slate-700">Slug <span class="text-xs text-slate-400">(auto-generated if empty)</span></label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $service->slug ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   maxlength="255">
            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Price --}}
        <div>
            <label for="price" class="block text-sm font-semibold text-slate-700">Price (৳) <span class="text-red-500">*</span></label>
            <input type="number" name="price" id="price" value="{{ old('price', $service->price ?? '0') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   required step="0.01" min="0">
            @error('price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- CTA Label --}}
        <div>
            <label for="cta_label" class="block text-sm font-semibold text-slate-700">CTA Label <span class="text-xs text-slate-400">(lead-gen; defaults to "Book Consultation")</span></label>
            <input type="text" name="cta_label" id="cta_label" value="{{ old('cta_label', $service->cta_label ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   maxlength="50" placeholder="Book Consultation">
            @error('cta_label') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Duration --}}
        <div>
            <label for="duration" class="block text-sm font-semibold text-slate-700">Duration <span class="text-xs text-slate-400">(e.g. "1 year", "8 weeks")</span></label>
            <input type="text" name="duration" id="duration" value="{{ old('duration', $service->duration ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   maxlength="100">
            @error('duration') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Sort Order --}}
        <div>
            <label for="sort_order" class="block text-sm font-semibold text-slate-700">Sort Order</label>
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $service->sort_order ?? $nextOrder ?? 0) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   min="0">
            @error('sort_order') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700">Status</label>
            <div class="mt-2 flex gap-6">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="status" value="1"
                           class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                           @checked(old('status', $service->status ?? true))>
                    Active
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="status" value="0"
                           class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                           @checked(!old('status', $service->status ?? true))>
                    Inactive
                </label>
            </div>
            @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Short Description --}}
    <div class="mt-6">
        <label for="short_description" class="block text-sm font-semibold text-slate-700">Short Description</label>
        <input type="text" name="short_description" id="short_description" value="{{ old('short_description', $service->short_description ?? '') }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
               maxlength="500">
        @error('short_description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Description --}}
    <div class="mt-6">
        <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
        <textarea name="description" id="description" rows="6"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">{{ old('description', $service->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Features (dynamic list) --}}
    <div class="mt-6">
        <label class="block text-sm font-semibold text-slate-700">Features <span class="text-xs text-slate-400">(displayed as a checklist)</span></label>
        <div class="mt-2 space-y-2">
            <template x-for="(feature, index) in features" :key="index">
                <div class="flex items-center gap-2">
                    <input type="text" name="features[]" x-model="features[index]" placeholder="Service feature"
                           class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    <button type="button" @click="features.splice(index, 1)"
                            class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Remove</button>
                </div>
            </template>
        </div>
        <button type="button" @click="features.push('')"
                class="mt-3 rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
            + Add Feature
        </button>
        <template x-if="features.length === 0">
            <p class="mt-1 text-xs text-slate-400">No features yet — click "Add Feature" to include items in the checklist.</p>
        </template>
        @error('features') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

