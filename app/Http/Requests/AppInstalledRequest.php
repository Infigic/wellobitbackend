<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppInstalledRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anonymous_id' => 'required|string|unique:user_devices,anonymous_id|max:255',
            'installed_at' => 'required|date',
            'timezone' => 'string',
            'locale' => 'string',
            'app_version' => 'string',
            'os_version' => 'string',
            'device_id' => 'string',
        ];
    }
}
