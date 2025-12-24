<?php

namespace App\Http\Requests\Planning;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'planned_duration' => ['sometimes', 'integer', 'min:15', 'max:480'],
            'priority' => ['sometimes', 'string', 'in:high,medium,low'],
            'study_type' => ['sometimes', 'string', 'in:review,new-material,practice'],
            'status' => ['sometimes', 'string', 'in:pending,in-progress,completed,missed'],
        ];
    }
}

