<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AssignRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!Gate::allows('assign-roles')) {
            return false;
        }

        $targetUser = User::findOrFail($this->route('id'));
        return $this->user()->can('assignRole', $targetUser);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => 'required|string|exists:roles,name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.required' => 'A role must be specified.',
            'role.exists' => 'The selected role does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'role' => 'role name',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $targetUserId = $this->route('id');
            $currentUserId = $this->user()->id;

            if ($targetUserId == $currentUserId) {
                $validator->errors()->add('user', 'You cannot assign roles to yourself.');
            }
        });
    }
}
