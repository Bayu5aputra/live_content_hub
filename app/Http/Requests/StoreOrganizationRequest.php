<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user is super admin
        return $this->user() && $this->user()->is_super_admin;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:organizations,slug|alpha_dash',
            'domain' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Organization name is required',
            'slug.unique' => 'This slug is already taken',
            'admin_name.required' => 'Admin name is required',
            'admin_email.required' => 'Admin email is required',
            'admin_password.required' => 'Admin password is required',
            'admin_password.min' => 'Admin password must be at least 8 characters',
        ];
    }
}
