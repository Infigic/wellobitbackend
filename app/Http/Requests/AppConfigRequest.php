<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppConfigRequest extends FormRequest
{
    public function rules()
    {
        return [
            'config_key' => [
                'required',
                'string',
                'regex:/^\S+$/',
                Rule::unique('app_configs', 'config_key')->ignore(optional($this->route('app_config'))->id),
            ],
            'value_type' => ['required', 'string', Rule::in(['string', 'boolean', 'integer', 'json'])],
            'config_value' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $type = $this->input('value_type');

                    if ($type === 'boolean') {
                        $normalized = strtolower((string) $value);
                        if (!in_array($normalized, ['true', 'false'], true)) {
                            $fail('Config value must be true or false when value type is boolean.');
                        }
                    }

                    if ($type === 'integer') {
                        $intValue = filter_var($value, FILTER_VALIDATE_INT);
                        if ($intValue === false || $intValue <= 0) {
                            $fail('Config value must be an integer greater than 0 when value type is integer.');
                        }
                    }

                    if ($type === 'json') {
                        json_decode($value);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Config value must be valid JSON when value type is json.');
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'config_key.regex' => 'Config key must not contain spaces.',
            'value_type.required' => 'Value type is required.',
            'value_type.in' => 'Value type must be one of: string, boolean, integer, json.',
            'config_value.required' => 'Config value is required.',
        ];
    }
}