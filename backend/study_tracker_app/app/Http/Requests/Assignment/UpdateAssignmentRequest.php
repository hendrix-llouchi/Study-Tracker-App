<?php

namespace App\Http\Requests\Assignment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['sometimes', 'date'],
            'priority' => ['sometimes', 'string', 'in:high,medium,low'],
            'status' => ['sometimes', 'string', 'in:pending,in-progress,completed,overdue'],
        ];
    }
}

