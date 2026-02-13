<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'lrn' => ['required', 'string', 'size:12', 'unique:students,lrn'],
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'religion' => ['nullable', 'string', 'max:50'],
            'learning_modality' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'father_name' => ['nullable', 'string', 'max:200'],
            'mother_name' => ['nullable', 'string', 'max:200'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_contact' => ['nullable', 'string', 'max:20'],
            'guardian_relationship' => ['nullable', 'string', 'max:100'],
            'previous_school' => ['nullable', 'string', 'max:255'],
        ];
    }
}
