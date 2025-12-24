<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'uuid', 'exists:courses,id'],
            'topic' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'planned_duration' => ['required', 'integer', 'min:15', 'max:480'],
            'priority' => ['required', 'string', 'in:high,medium,low'],
            'study_type' => ['required', 'string', 'in:review,new-material,practice'],
        ];
    }
}

