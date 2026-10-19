<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int|null $category_id
 * @property string $title
 * @property int|null $recurring_task_id
 * @property string|null $description
 * @property Carbon|null $task_date
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read User $user
 * @property-read RecurringTask|null $recurringTask
 *
 * @method static Builder<static>|Task newModelQuery()
 * @method static Builder<static>|Task newQuery()
 * @method static Builder<static>|Task query()
 * @method static Builder<static>|Task whereCategoryId($value)
 * @method static Builder<static>|Task whereCompletedAt($value)
 * @method static Builder<static>|Task whereCreatedAt($value)
 * @method static Builder<static>|Task whereDescription($value)
 * @method static Builder<static>|Task whereId($value)
 * @method static Builder<static>|Task whereTaskDate($value)
 * @method static Builder<static>|Task whereTitle($value)
 * @method static Builder<static>|Task whereUpdatedAt($value)
 * @method static Builder<static>|Task whereUserId($value)
 * @method static \Database\Factories\TaskFactory factory($count = null, $state = [])
 * @method static Builder<static>|Task filter(array $filters)
 * @method static Builder<static>|Task whereRecurringTaskId($value)
 * @method static Builder<static>|Task whereUuid($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['title', 'user_id', 'description', 'category_id', 'recurring_task_id', 'task_date'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory, HasUuids;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function uniqueIds()
    {
        return ['uuid'];
    }

    protected function casts()
    {
        return [
            'task_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    /**
     * Scope a query to the tasks matching the given filters.
     *
     * @param  array{status?: string|null, category?: string|null, from?: string|null, to?: string|null}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $status === 'complete'
                ? $query->whereNotNull('completed_at')
                : $query->whereNull('completed_at'))
            ->when($filters['category'] ?? null, fn (Builder $query, string $uuid) => $query->whereRelation('category', 'uuid', $uuid))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('task_date', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('task_date', '<=', $to));
    }

    /**
     * Mark the task as complete, or as incomplete when it is already complete.
     */
    public function toggleCompletion(): void
    {
        $this->completed_at = $this->completed_at ? null : now();

        $this->save();
    }
}
