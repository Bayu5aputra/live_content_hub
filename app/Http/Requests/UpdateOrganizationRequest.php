<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->route('organization');

        // User harus admin di organization ini
        return $this->user() && $this->user()->isAdminOf($organization->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $organization = $this->route('organization');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:organizations,slug,' . $organization->id,
            'domain' => 'nullable|string|max:255',
            'is_active' => 'boolean',
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
            'name.required' => 'Organization name is required',
            'name.max' => 'Organization name must not exceed 255 characters',
            'slug.required' => 'Organization slug is required',
            'slug.unique' => 'This slug is already taken',
            'slug.alpha_dash' => 'Slug can only contain letters, numbers, dashes and underscores',
            'domain.max' => 'Domain must not exceed 255 characters',
        ];
    }
}
