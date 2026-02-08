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
            'uuid' => 'required|uuid|unique:user_devices,uuid|max:255',
            'installed_at' => 'required|date',
            'timezone' => 'string',
            'locale' => 'string',
            'app_version' => 'string',
            'os_version' => 'string',
            'device_id' => 'string',
        ];
    }
}
