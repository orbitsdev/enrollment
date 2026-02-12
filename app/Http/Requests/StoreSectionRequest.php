<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
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
        $sectionId = $this->route('section')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $sectionId
                    ? 'unique:sections,name,' . $sectionId . ',id,semester_id,' . $this->semester_id
                    : 'unique:sections,name,NULL,id,semester_id,' . $this->semester_id,
            ],
            'strand_id' => ['required', 'exists:strands,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'grade_level' => ['required', 'in:11,12'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'adviser_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
