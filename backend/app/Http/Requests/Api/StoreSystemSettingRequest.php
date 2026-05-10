<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:160'],
            'label' => ['required', 'string', 'max:160'],
            'group' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:string,integer,number,boolean,json,array,enum,color,select,textarea,toggle'],
            'value' => ['nullable'],
            'default_value' => ['nullable'],
            'is_frontend' => ['sometimes', 'boolean'],
            'is_backend' => ['sometimes', 'boolean'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],
            'is_system' => ['sometimes', 'boolean'],
            'scope_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'scope_role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'scope_permission_id' => ['nullable', 'integer', 'exists:permissions,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $scopes = array_filter([
                $this->input('scope_user_id'),
                $this->input('scope_role_id'),
                $this->input('scope_permission_id'),
            ], fn ($value) => $value !== null && $value !== '');

            if (count($scopes) > 1) {
                $validator->errors()->add('scope', 'Only one scope override is allowed per setting record.');
            }
        });
    }
}

