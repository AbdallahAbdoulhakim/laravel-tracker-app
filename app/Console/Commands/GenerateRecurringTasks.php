<?php

namespace App\Console\Commands;

use App\Enums\TaskFrequency;
use App\Models\RecurringTask;
use App\Models\Task;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

#[Signature('app:generate-recurring-tasks {date? : The day to generate tasks for}')]
#[Description('Command description')]
class GenerateRecurringTasks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Determine which date we're generating the tasks for (default it to today)
        $targetDate = $this->argument('date')
            ? Carbon::parse($this->argument('date'))
            : today();

        // 2. Find all recurring task templates that are within their date range and are not marked as soft deleted

        $recurringTasks = RecurringTask::query()
            ->where(fn (Builder $query) => $query->whereNull('start_date')
                ->orWhere('start_date', '<=', $targetDate)
            )
            ->where(fn (Builder $query) => $query->whereNull('end_date')
                ->orWhere('end_date', '>=', $targetDate))
            ->get();

        if ($recurringTasks->isEmpty()) {
            $this->info('No active recurring tasks found.');

            return self::FAILURE;
        }

        $this->info("Processing {$recurringTasks->count()} recurring task templates ...");

        $created = 0;
        $skipped = 0;

        foreach ($recurringTasks as $recurringTask) {
            // 3. For each recurring template, check if we should generate a task for this date based on frequency rules
            if (! $this->isRecurringTaskDue($recurringTask, $targetDate)) {
                $skipped++;

                continue;
            }

            // 4. Verify we haven't already generated this task to prevent duplicates
            $exists = Task::query()->where('recurring_task_id', $recurringTask->id)
                ->whereDate('task_date', $targetDate)->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            // 5. Create the task instance

            Task::create([
                'user_id' => $recurringTask->user_id,
                'category_id' => $recurringTask->category_id,
                'title' => $recurringTask->title,
                'description' => $recurringTask->description,
                'recurring_task_id' => $recurringTask->id,
                'task_date' => $targetDate,
            ]);

            $created++;

            // 6. Check if any recurring tasks have passed their end date and soft delte them

        }

        $expired = RecurringTask::query()->whereNotNull('end_date')->where('end_date', '<', $targetDate)->delete();
        // 7. Output feedback so we know what happened

        $this->info("Created {$created} recurring tasks.");

        if ($skipped > 0) {
            $this->warn("Skipped {$skipped} recurring tasks.");
        }

        if ($expired > 0) {
            $this->info("Archived {$expired} expired recurring tasks.");
        }

        $this->newLine();

        return self::SUCCESS;

    }

    private function isRecurringTaskDue(RecurringTask $recurringTask, Carbon $targetDate)
    {
        return match ($recurringTask->frequency) {
            TaskFrequency::Daily => true,
            TaskFrequency::Weekdays => $targetDate->isWeekday(),
            TaskFrequency::Weekly => $this->isWeeklyRecurringTaskDue($recurringTask, $targetDate),
            TaskFrequency::Monthly => $this->isMonthlyRecurringTaskDue($recurringTask, $targetDate),
            'default' => false
        };
    }

    private function isWeeklyRecurringTaskDue(RecurringTask $recurringTask, Carbon $targetDate)
    {
        $config = $recurringTask->frequency_config;

        if (! $config || ! isset($config['days']) || ! is_array($config['days'])) {
            return false;
        }

        return in_array(strtolower($targetDate->englishDayOfWeek), $config['days']);
    }

    private function isMonthlyRecurringTask(RecurringTask $recurringTask, Carbon $targetDate)
    {
        $config = $recurringTask->frequency_config;

        if (! $config || ! isset($config['day'])) {
            return false;
        }

        $daysOfMonth = min($config['day'], $targetDate->daysInMonth);

        return $targetDate->day == $daysOfMonth;
    }
}
