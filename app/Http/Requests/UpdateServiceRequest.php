<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')->id;

        return [
            'name'               => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:255', "unique:services,slug,{$serviceId}"],
            'short_description'  => ['nullable', 'string', 'max:500'],
            'description'        => ['nullable', 'string'],
            'features'           => ['nullable', 'array'],
            'features.*'         => ['nullable', 'string', 'max:500'],
            'price'              => ['required', 'numeric', 'min:0'],
            'cta_label'          => ['nullable', 'string', 'max:50'],
            'duration'           => ['nullable', 'string', 'max:100'],
            'status'             => ['sometimes', 'boolean'],
            'sort_order'         => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function validatedData(): array
    {
        $data = $this->validated();

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['status'] = $this->boolean('status');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['features'] = array_values(array_filter($data['features'] ?? []));

        $data['description'] = $data['description']
            ? strip_tags($data['description'], '<p><a><strong><em><ul><ol><li><h2><h3><h4><img><br><table><thead><tbody><tr><td><th>')
            : null;

        return $data;
    }
}
