<?php

namespace App\Http\Requests\Performance;

use Illuminate\Foundation\Http\FormRequest;

class CreateResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'uuid', 'exists:courses,id'],
            'assessment_type' => ['required', 'string', 'in:quiz,midterm,final,assignment,project'],
            'assessment_name' => ['nullable', 'string', 'max:255'],
            'score' => ['required', 'numeric', 'min:0'],
            'max_score' => ['required', 'numeric', 'min:0'],
            'grade' => ['nullable', 'string', 'max:5'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'semester' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->score > $this->max_score) {
                $validator->errors()->add('score', 'Score cannot exceed max score.');
            }
        });
    }
}

