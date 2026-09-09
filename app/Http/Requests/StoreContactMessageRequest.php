<?php

namespace App\Http\Requests;

use App\Rules\BangladeshiPhone;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', new BangladeshiPhone],
            'email' => ['required', 'email', 'max:255'],
            'topic' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
