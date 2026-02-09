<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppleWatchConnectedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:user_trackings,user_id',
            'has_apple_watch' => 'nullable|boolean',
            'apple_watch_model' => ['nullable', Rule::in(config('event.apple_watch_model'))],
            'apple_watch_os_version' => 'nullable|string|max:50',
            'apple_health_connected' => 'nullable|boolean',
        ];
    }

}
