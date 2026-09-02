<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('course')->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', "unique:courses,slug,{$courseId}"],
            'category_id' => ['nullable', 'exists:categories,id'],
            'hsk_level' => ['nullable', 'string', 'max:20'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_weeks' => ['nullable', 'integer', 'min:1'],
            'batch_start_date' => ['nullable', 'date'],
            'batch_end_date' => ['nullable', 'date', 'after_or_equal:batch_start_date'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'is_published' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'remove_thumbnail' => ['sometimes', 'boolean'],
        ];
    }

    public function validatedData(): array
    {
        $data = $this->validated();

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_published'] = $this->boolean('is_published');
        $data['is_featured'] = $this->boolean('is_featured');
        $data['description'] = strip_tags($data['description'], '<p><a><strong><em><ul><ol><li><h2><h3><h4><img><br><table><thead><tbody><tr><td><th>');

        // Remove thumbnail file and flag from data array; handled separately
        unset($data['thumbnail'], $data['remove_thumbnail']);

        return $data;
    }
}
