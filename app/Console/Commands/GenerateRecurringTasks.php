<?php

namespace App\Console\Commands;

use App\Enums\TaskFrequency;
use App\Models\RecurringTask;
use App\Models\Task;
use DateTime;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Signature('app:generate-recurring-tasks {date? : The day to generate tasks for}')]
#[Description('Command description')]
class GenerateRecurringTasks extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $created = 0;
        $skipped = 0;

         // 1. Determine which date we're generating the tasks for (default it to today)
        $targetDate = $this->argument('date')
            ? Carbon::parse($this->argument('date'))
            : today();

        $recurringTasksQuery = RecurringTask::query()
            ->where(fn(Builder $query) => $query->whereNull('start_date')->orWhere('start_date','<=', $targetDate))
            ->where(fn(Builder $query)=> $query->whereNull('end_date')->orWhere('end_date','>=', $targetDate))
            ->whereDoesntHave('tasks', fn(Builder $query)=> $query->whereDate('task_date', $targetDate));


        if (!$recurringTasksQuery->count()) {
            $this->info('No active recurring tasks found.');

            return self::FAILURE;
        }


        $this->info("Processing {$recurringTasksQuery->count()} recurring task templates ...");

       $recurringTasksQuery->chunkById(250, function(Collection $recurringTasks) use($targetDate, &$skipped, &$created){
            try {
                $insertTasksBatch = [];
                foreach ($recurringTasks as $recurringTask){
                    try {
                        if (!$this->isRecurringTaskDue($recurringTask, $targetDate)){
                            $skipped ++;
                            continue;
                        }

                        $now = new DateTime();

                        $insertTasksBatch[] = [
                            'uuid' => (string) Str::uuid(),
                            'user_id' => $recurringTask->user_id,
                            'category_id' => $recurringTask->category_id,
                            'title' => $recurringTask->title,
                            'description' => $recurringTask->description,
                            'recurring_task_id' => $recurringTask->id,
                            'task_date' => $targetDate,
                            'created_at' => $now,
                            'updated_at' => $now

                        ];

                        if ($insertTasksBatch) {
                            Task::insert($insertTasksBatch);
                            $created += count($insertTasksBatch);
                        }
                    } catch (\Exception $err) {
                       report($err);
                    }
                }
            } catch (\Exception $err) {
                report($err);
            }

       });
        
        // 7. Output feedback so we know what happened

        $this->info("Created {$created} recurring tasks.");

        if ($skipped > 0) {
            $this->warn("Skipped {$skipped} recurring tasks.");
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
