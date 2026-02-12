<?php

namespace App\Http\Requests;

use App\Enums\SubjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'code' => [
                'required',
                'string',
                Rule::unique('subjects', 'code')->ignore($subjectId),
            ],
            'name' => 'required|string',
            'type' => ['required', Rule::in(collect(SubjectType::cases())->pluck('value'))],
            'hours' => 'required|integer|min:1',
            'prerequisite_id' => 'nullable|exists:subjects,id',
            'strands' => 'nullable|array',
            'strands.*.strand_id' => 'required|exists:strands,id',
            'strands.*.grade_level' => 'required|in:11,12',
            'strands.*.semester' => 'required|in:1,2',
        ];
    }
}
