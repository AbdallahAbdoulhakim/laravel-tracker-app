<?php

namespace App\Http\Requests;

use App\Enums\TaskFrequency;
use App\Models\RecurringTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTaskRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['bail', 'nullable', 'uuid', Rule::exists('categories', 'uuid')->where('user_id', $this->user()->id)],
            'frequency' => ['required', Rule::enum(TaskFrequency::class)],
            'days' => ['exclude_unless:frequency,'.TaskFrequency::Weekly->value, 'required', 'array'],
            'days.*' => ['exclude_unless:frequency,'.TaskFrequency::Weekly->value, 'string', 'distinct', Rule::in(array_keys(RecurringTask::WEEKDAYS))],
            'day_of_month' => ['exclude_unless:frequency,'.TaskFrequency::Monthly->value, 'required', 'integer', 'between:1,31'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'days.required' => __('Select at least one day of the week.'),
        ];
    }

    /**
     * Get the validated recurring task attributes, with the category UUID resolved to its key
     * and the frequency specific fields grouped into the frequency configuration.
     *
     * @return array{title: string, description: string|null, category_id: int|null, frequency: string, frequency_config: array{days?: list<string>, day?: int}|null, start_date: string|null, end_date: string|null}
     */
    public function recurringTaskAttributes(): array
    {
        $frequency = $this->enum('frequency', TaskFrequency::class);

        return [
            ...$this->safe()->only(['title', 'description', 'frequency', 'start_date', 'end_date']),
            'category_id' => $this->user()->categories()->where('uuid', $this->validated('category'))->value('id'),
            'frequency_config' => match ($frequency) {
                TaskFrequency::Weekly => ['days' => array_values(array_intersect(array_keys(RecurringTask::WEEKDAYS), $this->validated('days')))],
                TaskFrequency::Monthly => ['day' => (int) $this->validated('day_of_month')],
                default => null,
            },
        ];
    }
}
