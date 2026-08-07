<div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Title --}}
        <div>
            <label for="title" class="block text-sm font-semibold text-slate-700">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title', $post->title ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   required maxlength="255">
            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label for="slug" class="block text-sm font-semibold text-slate-700">Slug <span class="text-xs text-slate-400">(auto-generated from title)</span></label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                   maxlength="255">
            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Category with inline "Add New" --}}
        <div>
            <label for="category_id" class="block text-sm font-semibold text-slate-700">Category <span class="text-red-500">*</span></label>
            <div class="mt-1 flex gap-2">
                <select name="category_id" id="category_id"
                        class="block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $post->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="button" id="addNewCategoryBtn"
                        class="shrink-0 rounded-lg bg-slate-100 px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200"
                        title="Add New Category">
                    + New
                </button>
            </div>
            <div id="newCategoryInput" class="mt-2 hidden flex gap-2">
                <input type="text" id="newCategoryName" placeholder="Category name"
                       class="block w-full rounded-lg border border-slate-300 px-4 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                <button type="button" id="saveNewCategoryBtn"
                        class="shrink-0 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Save
                </button>
                <button type="button" id="cancelNewCategoryBtn"
                        class="shrink-0 rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200">
                    Cancel
                </button>
            </div>
            @error('category_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Published At --}}
        <div>
            <label for="published_at" class="block text-sm font-semibold text-slate-700">Published Date</label>
            <input type="datetime-local" name="published_at" id="published_at"
                   value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
            @error('published_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Excerpt --}}
    <div class="mt-6">
        <label for="excerpt" class="block text-sm font-semibold text-slate-700">Excerpt <span class="text-xs text-slate-400">(short summary, max 500 chars)</span></label>
        <textarea name="excerpt" id="excerpt" rows="3" maxlength="500"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
        @error('excerpt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Content --}}
    <div class="mt-6">
        <label for="content" class="block text-sm font-semibold text-slate-700">Content <span class="text-red-500">*</span></label>
        <textarea name="content" id="content" rows="12"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                  required>{{ old('content', $post->content ?? '') }}</textarea>
        @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Featured Image --}}
    <div class="mt-6">
        <label class="block text-sm font-semibold text-slate-700">Featured Image <span class="text-xs text-slate-400">(max 2MB, jpg/png/webp)</span></label>

        @if(isset($post) && $post->featured_image)
            <div class="mt-2 flex items-center gap-4">
                <img src="{{ Storage::disk('public')->url($post->featured_image) }}" alt="Current featured image"
                     class="h-24 w-40 rounded-lg border border-slate-200 object-cover shadow-sm">
                <p class="text-xs text-slate-500">Upload a new image to replace the current one.</p>
            </div>
        @endif

        <input type="file" name="featured_image" id="featured_image" accept="image/*"
               class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
        @error('featured_image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>

    {{-- Status --}}
    <div class="mt-6">
        <label class="block text-sm font-semibold text-slate-700">Status</label>
        <div class="mt-2 flex gap-6">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="radio" name="is_published" value="1"
                       class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                       @checked(old('is_published', $post->is_published ?? true))>
                Published
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="radio" name="is_published" value="0"
                       class="border-slate-300 text-emerald-600 focus:ring-emerald-500"
                       @checked(!old('is_published', $post->is_published ?? true))>
                Draft
            </label>
        </div>
        @error('is_published') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Slug auto-generation script --}}
<script>
    document.getElementById('title').addEventListener('input', function() {
        const slugInput = document.getElementById('slug');
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manual = this.value.length > 0;
    });

    // Category quick-add
    document.getElementById('addNewCategoryBtn').addEventListener('click', function() {
        document.getElementById('newCategoryInput').classList.remove('hidden');
        this.parentElement.previousElementSibling.classList.add('hidden');
    });
    document.getElementById('cancelNewCategoryBtn').addEventListener('click', function() {
        document.getElementById('newCategoryInput').classList.add('hidden');
        document.getElementById('addNewCategoryBtn').parentElement.classList.remove('hidden');
    });
    document.getElementById('saveNewCategoryBtn').addEventListener('click', function() {
        const name = document.getElementById('newCategoryName').value.trim();
        if (!name) return;

        fetch('{{ route("admin.categories.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name: name })
        })
        .then(r => r.json())
        .then(data => {
            if (data.id) {
                const select = document.getElementById('category_id');
                const opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.name;
                opt.selected = true;
                select.appendChild(opt);
                document.getElementById('newCategoryName').value = '';
                document.getElementById('newCategoryInput').classList.add('hidden');
                document.getElementById('addNewCategoryBtn').parentElement.classList.remove('hidden');
            } else {
                alert('Error: ' + (data.message || 'Could not create category'));
            }
        })
        .catch(err => {
            alert('Network error. Please try again.');
        });
    });
</script>
