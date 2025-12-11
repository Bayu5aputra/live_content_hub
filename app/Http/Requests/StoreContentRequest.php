<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
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
            'type' => 'required|in:image,video,pdf',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:51200', // 50MB max
            'thumbnail' => 'nullable|image|max:2048', // 2MB max
            'duration' => 'required|integer|min:1',
            'order' => 'nullable|integer|min:0',
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
            'type.required' => 'Content type is required',
            'type.in' => 'Content type must be image, video, or pdf',
            'title.required' => 'Content title is required',
            'title.max' => 'Content title must not exceed 255 characters',
            'file.required' => 'File is required',
            'file.max' => 'File size must not exceed 50MB',
            'thumbnail.image' => 'Thumbnail must be an image',
            'thumbnail.max' => 'Thumbnail size must not exceed 2MB',
            'duration.required' => 'Duration is required',
            'duration.integer' => 'Duration must be a number',
            'duration.min' => 'Duration must be at least 1 second',
            'order.integer' => 'Order must be a number',
            'order.min' => 'Order must be at least 0',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string boolean to actual boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
