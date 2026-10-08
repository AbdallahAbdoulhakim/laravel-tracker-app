<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $title
 * @property string|null $description
 * @property bool $is_recurring
 * @property Carbon|null $task_date
 * @property string|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read User $user
 * @method static Builder<static>|Task newModelQuery()
 * @method static Builder<static>|Task newQuery()
 * @method static Builder<static>|Task query()
 * @method static Builder<static>|Task whereCategoryId($value)
 * @method static Builder<static>|Task whereCompletedAt($value)
 * @method static Builder<static>|Task whereCreatedAt($value)
 * @method static Builder<static>|Task whereDescription($value)
 * @method static Builder<static>|Task whereId($value)
 * @method static Builder<static>|Task whereIsRecurring($value)
 * @method static Builder<static>|Task whereTaskDate($value)
 * @method static Builder<static>|Task whereTitle($value)
 * @method static Builder<static>|Task whereUpdatedAt($value)
 * @method static Builder<static>|Task whereUserId($value)
 * @mixin \Eloquent
 */
class Task extends Model
{
    protected function casts()
    {
        return [
            'is_recurring' => 'boolean',
            'is_completed' => 'boolean',
            'task_date' => 'datetime',
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
}
