<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'category_id' => ['required', 'exists:categories,id'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function validatedForStore(): array
    {
        $data = $this->validated();

        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']);
        $data['is_published'] = $this->boolean('is_published');
        $data['user_id'] = $this->user()->id;
        $data['published_at'] = $data['published_at'] ?? ($data['is_published'] ? now() : null);
        $data['content'] = strip_tags($data['content'], '<p><a><strong><em><ul><ol><li><h2><h3><h4><img><br><table><thead><tbody><tr><td><th>');

        // Handle image upload
        if ($this->hasFile('featured_image')) {
            $data['featured_image'] = $this->file('featured_image')->store('uploads/blog', 'public');
        } else {
            unset($data['featured_image']);
        }

        return $data;
    }
}
