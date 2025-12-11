<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = $this->route('organization');
        $content = $this->route('content');

        // Check content belongs to organization
        if ($content->organization_id !== $organization->id) {
            return false;
        }

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
            'type' => 'sometimes|in:image,video,pdf',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'file' => 'sometimes|file|max:51200', // 50MB max
            'thumbnail' => 'sometimes|image|max:2048', // 2MB max
            'duration' => 'sometimes|integer|min:1',
            'order' => 'sometimes|integer|min:0',
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
            'type.in' => 'Content type must be image, video, or pdf',
            'title.max' => 'Content title must not exceed 255 characters',
            'file.max' => 'File size must not exceed 50MB',
            'thumbnail.image' => 'Thumbnail must be an image',
            'thumbnail.max' => 'Thumbnail size must not exceed 2MB',
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
