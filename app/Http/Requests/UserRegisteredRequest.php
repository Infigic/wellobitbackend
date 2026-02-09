<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRegisteredRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => 'required|string|exists:user_devices,uuid|max:255',
            'email' => 'required|unique:user_trackings,email|email|max:255',
            'first_name' => 'required|string|max:100',
            'consent_email' => 'required|boolean',
            'signup_method' => ['required', Rule::in(config('event.signup_method'))],
            'signup_source' => 'required|string|max:50',
        ];
    }
}
