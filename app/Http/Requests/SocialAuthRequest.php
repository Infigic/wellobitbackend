<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => 'required|in:google,apple',
            'token'    => 'required|string',
            'email'    => 'nullable|email',
            'name'     => 'nullable|string',

            'uuid' => 'required|string',
            'signup_source' => 'required|string',
            'consent_email' => 'required|boolean',
        ];
    }

}
