<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    private const PHONE_FORMAT = 'regex:/^\+?[0-9][0-9\s()-]{6,19}$/';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', self::PHONE_FORMAT, Rule::unique(User::class)->ignore($this->user()->id)],
            'secondary_phone' => ['nullable', 'string', self::PHONE_FORMAT, 'different:phone'],
            // SVG is left out on purpose: it can carry scripts.
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => __('app.primary_phone'),
            'secondary_phone' => __('app.secondary_phone'),
            'avatar' => __('app.profile_picture'),
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('app.invalid_phone'),
            'secondary_phone.regex' => __('app.invalid_phone'),
            'secondary_phone.different' => __('app.phones_must_differ'),
        ];
    }
}
