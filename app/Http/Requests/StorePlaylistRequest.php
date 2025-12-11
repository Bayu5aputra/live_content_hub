<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->route('organization');

        // User harus minimal editor di organization ini
        return $this->user() && $this->user()->hasAccessToOrganization($organization->id, 'editor');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'loop' => 'boolean',
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
            'name.required' => 'Playlist name is required',
            'name.max' => 'Playlist name must not exceed 255 characters',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'is_active' => $this->has('is_active')
                ? filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)
                : true,
            'loop' => $this->has('loop')
                ? filter_var($this->loop, FILTER_VALIDATE_BOOLEAN)
                : true,
        ]);
    }
}
