<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name'           => 'sometimes|string|max:255',
            'phone_number'   => 'sometimes|nullable|string|max:20',
            'skill_level_id' => 'sometimes|nullable|exists:skill_levels,id',
            'avatar'         => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password'       => 'sometimes|nullable|string|min:6|confirmed',
        ];
    }

    /**
     * Handle method spoofing for multipart requests
     */
    protected function prepareForValidation(): void
    {
        // Laravel doesn't handle PUT with multipart/form-data well
        // So we need to merge all input including files
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $input = $this->all();

            // Handle files separately for PUT requests
            if ($this->hasFile('avatar')) {
                $input['avatar'] = $this->file('avatar');
            }

            // Handle password confirmation if exists
            if ($this->has('password_confirmation')) {
                $input['password_confirmation'] = $this->input('password_confirmation');
            }

            $this->merge($input);
        }
    }
    // protected function prepareForValidation(): void
    // {
    //     // Hanya merge file jika ada file avatar
    //     if (($this->isMethod('PUT') || $this->isMethod('PATCH')) && $this->hasFile('avatar')) {
    //         $input = $this->all();
    //         $input['avatar'] = $this->file('avatar');

    //         if ($this->has('password_confirmation')) {
    //             $input['password_confirmation'] = $this->input('password_confirmation');
    //         }

    //         $this->merge($input);
    //     }
    // }

    /**
     * Override to make sure non-validated fields are not removed
     */
    protected function passedValidation()
    {
        \Illuminate\Support\Facades\Log::debug('Form data passed validation', $this->all());
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string'           => 'Name must be a string',
            'name.max'              => 'Name must not exceed 255 characters',
            'phone_number.string'   => 'Phone number must be a string',
            'phone_number.max'      => 'Phone number must not exceed 20 characters',
            'skill_level_id.exists' => 'Selected skill level does not exist',
            'avatar.image'          => 'Avatar must be an image file',
            'avatar.mimes'          => 'Avatar must be jpeg, png, jpg, or gif format',
            'avatar.max'            => 'Avatar file size must not exceed 2MB',
            'password.min'          => 'Password must be at least 6 characters',
            'password.confirmed'    => 'Password confirmation does not match',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
