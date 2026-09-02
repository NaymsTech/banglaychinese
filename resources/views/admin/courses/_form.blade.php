<div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Title --}}
        <div>
            <label for="title" class="block text-sm font-semibold text-slate-700">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title', $course->title ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   required maxlength="255">
            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label for="slug" class="block text-sm font-semibold text-slate-700">Slug <span class="text-xs text-slate-400">(auto-generated if empty)</span></label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $course->slug ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   maxlength="255">
            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Category --}}
        <div>
            <label for="category_id" class="block text-sm font-semibold text-slate-700">Category</label>
            <select name="category_id" id="category_id"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $course->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- HSK Level --}}
        <div>
            <label for="hsk_level" class="block text-sm font-semibold text-slate-700">HSK Level</label>
            <select name="hsk_level" id="hsk_level"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                <option value="">-- Select HSK Level --</option>
                @foreach(['HSK 1', 'HSK 2', 'HSK 3', 'HSK 4', 'HSK 5', 'HSK 6'] as $level)
                    <option value="{{ $level }}" @selected(old('hsk_level', $course->hsk_level ?? '') == $level)>{{ $level }}</option>
                @endforeach
            </select>
            @error('hsk_level') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Price --}}
        <div>
            <label for="price" class="block text-sm font-semibold text-slate-700">Price (৳) <span class="text-red-500">*</span></label>
            <input type="number" name="price" id="price" value="{{ old('price', $course->price ?? '0') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   required step="0.01" min="0">
            @error('price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Duration Weeks --}}
        <div>
            <label for="duration_weeks" class="block text-sm font-semibold text-slate-700">Duration (weeks)</label>
            <input type="number" name="duration_weeks" id="duration_weeks" value="{{ old('duration_weeks', $course->duration_weeks ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   min="1">
            @error('duration_weeks') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

    </div>

    {{-- Batch Dates --}}
    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="batch_start_date" class="block text-sm font-semibold text-slate-700">Batch Start Date</label>
            <input type="date" name="batch_start_date" id="batch_start_date" value="{{ old('batch_start_date', isset($course) ? optional($course->batch_start_date)->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            @error('batch_start_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="batch_end_date" class="block text-sm font-semibold text-slate-700">Batch End Date</label>
            <input type="date" name="batch_end_date" id="batch_end_date" value="{{ old('batch_end_date', isset($course) ? optional($course->batch_end_date)->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            @error('batch_end_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Description --}}
    <div class="mt-6">
        <label for="description" class="block text-sm font-semibold text-slate-700">Description <span class="text-red-500">*</span></label>
        <textarea name="description" id="description" rows="6"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                  required>{{ old('description', $course->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Thumbnail --}}
    <div class="mt-6">
        <label class="block text-sm font-semibold text-slate-700">Thumbnail <span class="text-xs text-slate-400">(max 2MB, jpg/png/webp)</span></label>

        @if(isset($course) && $course->thumbnail)
            <div class="mt-2 flex items-center gap-4">
                <img src="{{ Storage::disk('public')->url($course->thumbnail) }}" alt="Current thumbnail"
                     class="h-24 w-40 rounded-lg border border-slate-200 object-cover shadow-sm">
                <label class="flex items-center gap-2 text-sm text-red-600 cursor-pointer select-none">
                    <input type="checkbox" name="remove_thumbnail" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                    Remove current thumbnail
                </label>
            </div>
        @endif

        <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
               class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
        @error('thumbnail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Status & Featured --}}
    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-semibold text-slate-700">Status</label>
            <div class="mt-2 flex gap-6">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="is_published" value="1"
                           class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                           @checked(old('is_published', $course->is_published ?? true))>
                    Published
                </label>
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="radio" name="is_published" value="0"
                           class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                           @checked(!old('is_published', $course->is_published ?? true))>
                    Draft
                </label>
            </div>
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1"
                       class="rounded border-slate-300 text-amber-500 focus:ring-amber-500"
                       @checked(old('is_featured', $course->is_featured ?? false))>
                Featured Course
            </label>
            @error('is_featured') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
