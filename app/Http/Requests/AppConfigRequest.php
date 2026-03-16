<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppConfigRequest extends FormRequest
{
    protected $redirectRoute = 'app-configs.index';

    public function rules()
    {
        return [
            'config_key' => [
                'required',
                'string',
                Rule::in(array_keys(config('app_config'))),
                Rule::unique('app_configs', 'config_key')->ignore(optional($this->route('app_config'))->id),
            ],            
            'config_value' => 'nullable|string',
            'value_type' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}