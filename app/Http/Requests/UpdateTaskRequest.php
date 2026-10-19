<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends StoreTaskRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->can('manage', $this->task);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['bail', 'nullable', 'uuid', Rule::exists('categories', 'uuid')->where('user_id', $this->user()->id)],
            'task_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Get the validated task attributes, with the category UUID resolved to its key.
     *
     * @return array<string, mixed>
     */
    public function taskAttributes(): array
    {
        return [
            ...$this->safe()->except('category'),
            'category_id' => $this->user()->categories()->where('uuid', $this->validated('category'))->value('id'),
        ];
    }
}
