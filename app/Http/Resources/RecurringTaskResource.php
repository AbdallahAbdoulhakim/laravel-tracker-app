<?php

namespace App\Http\Resources;

use App\Enums\TaskFrequency;
use App\Models\RecurringTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RecurringTask
 */
class RecurringTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->uuid,
                'name' => $this->category->name,
            ] : null),
            'frequency' => $this->frequency?->value,
            'schedule' => $this->schedule(),
            'days' => $this->frequency_config['days'] ?? [],
            'day_of_month' => $this->frequency_config['day'] ?? null,
            'start_date' => $this->start_date?->format('M d, Y'),
            'end_date' => $this->end_date?->format('M d, Y'),
        ];
    }

    /**
     * Describe the frequency of the recurring task in a human readable way.
     */
    private function schedule(): string
    {
        return match ($this->frequency) {
            TaskFrequency::Daily => __('Every day'),
            TaskFrequency::Weekdays => __('Every weekday'),
            TaskFrequency::Weekly => __('Weekly on :days', [
                'days' => collect($this->frequency_config['days'] ?? [])
                    ->map(fn (string $day) => __(RecurringTask::WEEKDAYS[$day] ?? $day))
                    ->implode(', '),
            ]),
            TaskFrequency::Monthly => __('Monthly on day :day', ['day' => $this->frequency_config['day'] ?? '—']),
            default => '—',
        };
    }
}
