<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcquisitionInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => 'required|string|exists:user_devices,uuid|max:255',
            'acquisition_channel' => ['nullable', Rule::in(config('event.acquisition_channel'))],
            'acquisition_source' => ['nullable', Rule::in(config('event.acquisition_source'))],
            'campaign_name' => 'nullable|string|max:255',
        ];
    }
}
