<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:bkash,nagad,free'],
            'transaction_id' => [
                Rule::requiredIf(fn (): bool => $this->input('payment_method') !== 'free'),
                'string',
                'min:8',
                'max:64',
            ],
            'sender_number' => [
                Rule::requiredIf(fn (): bool => $this->input('payment_method') !== 'free'),
                'regex:/^(?:\+88|88)?(01[3-9]\d{8})$/',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'পেমেন্ট পদ্ধতি নির্বাচন করুন।',
            'payment_method.in' => 'শুধুমাত্র বিকাশ (bKash), নগদ (Nagad) অথবা ফ্রি এনরোলমেন্ট গ্রহণযোগ্য।',
            'transaction_id.required' => 'ট্রানজেকশন আইডি প্রদান করুন।',
            'transaction_id.min' => 'ট্রানজেকশন আইডি কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'transaction_id.max' => 'ট্রানজেকশন আইডি সর্বোচ্চ ৬৪ অক্ষরের হতে হবে।',
            'sender_number.required' => 'প্রেরক (Sender) মোবাইল নম্বর প্রদান করুন।',
            'sender_number.regex' => 'সঠিক বাংলাদেশী মোবাইল নম্বর প্রদান করুন (যেমন: 01XXXXXXXXX)।',
        ];
    }
}
