<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LevelUpRequest extends FormRequest
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
            'target_level'        => 'required|string|exists:skill_levels,level_name',
            'documents'           => 'required|array|min:2', // At least KTP and one additional document
            'documents.ktp'       => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
            'documents.ijazah'    => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'documents.sertifikat' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'documents.portofolio' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240', // 10MB for portfolio
            'notes'               => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'target_level.required'     => 'Target level is required',
            'target_level.exists'       => 'Selected target level does not exist',
            'documents.required'        => 'Documents are required',
            'documents.min'             => 'At least 2 documents are required (KTP and one additional document)',
            'documents.ktp.required'    => 'KTP document is required',
            'documents.ktp.file'        => 'KTP must be a file',
            'documents.ktp.mimes'       => 'KTP must be jpeg, png, jpg, or pdf format',
            'documents.ktp.max'         => 'KTP file size must not exceed 5MB',
            'documents.ijazah.file'     => 'Ijazah must be a file',
            'documents.ijazah.mimes'    => 'Ijazah must be jpeg, png, jpg, or pdf format',
            'documents.ijazah.max'      => 'Ijazah file size must not exceed 5MB',
            'documents.sertifikat.file' => 'Sertifikat must be a file',
            'documents.sertifikat.mimes' => 'Sertifikat must be jpeg, png, jpg, or pdf format',
            'documents.sertifikat.max'  => 'Sertifikat file size must not exceed 5MB',
            'documents.portofolio.file' => 'Portofolio must be a file',
            'documents.portofolio.mimes' => 'Portofolio must be jpeg, png, jpg, or pdf format',
            'documents.portofolio.max'  => 'Portofolio file size must not exceed 10MB',
            'notes.max'                 => 'Notes must not exceed 1000 characters',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Check if at least one additional document besides KTP is provided
            $documents = $this->file('documents', []);
            $hasAdditionalDoc = isset($documents['ijazah']) || isset($documents['sertifikat']) || isset($documents['portofolio']);

            if (!$hasAdditionalDoc) {
                $validator->errors()->add('documents', 'At least one additional document (ijazah, sertifikat, or portofolio) besides KTP is required.');
            }
        });
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
