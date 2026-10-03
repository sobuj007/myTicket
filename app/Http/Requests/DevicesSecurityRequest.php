<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DevicesSecurityRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'device_id' => [
                'nullable',
                'string',
                'max:191',
            ],

            'platform' => [
                'nullable',
                'string',
                'in:android,ios,windows,macos,linux,web',
            ],

            'app_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'app_version' => [
                'nullable',
                'string',
                'max:50',
            ],

            'build_number' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }
}
