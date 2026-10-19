<?php

namespace App\Http\Controllers;

use App\Enums\TaskFrequency;
use App\Http\Requests\StoreRecurringTaskRequest;
use App\Http\Requests\UpdateRecurringTaskRequest;
use App\Models\RecurringTask;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RecurringTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $recurringTasks = $request->user()->recurringTasks()
            ->with('category')
            ->latest()
            ->latest('id')
            ->paginate(10);

        return view('recurring-tasks.index', [
            'recurringTasks' => $recurringTasks->toResourceCollection()->resolve(),
            'links' => fn () => $recurringTasks->links(),
            'hasPages' => $recurringTasks->hasPages(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('recurring-tasks.create', [
            'categories' => $this->categoryOptions($request),
            'frequencies' => $this->frequencyOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecurringTaskRequest $request): RedirectResponse
    {
        $request->user()->recurringTasks()->create($request->recurringTaskAttributes());

        return to_route('recurring-tasks.index')->with('success', 'Recurring task created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, RecurringTask $recurringTask): View
    {
        return view('recurring-tasks.edit', [
            'recurringTask' => $recurringTask->load('category')->toResource()->resolve(),
            'categories' => $this->categoryOptions($request),
            'frequencies' => $this->frequencyOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecurringTaskRequest $request, RecurringTask $recurringTask): RedirectResponse
    {
        $recurringTask->update($request->recurringTaskAttributes());

        return to_route('recurring-tasks.index')->with('success', 'Recurring task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringTask $recurringTask): RedirectResponse
    {
        $recurringTask->delete();

        return to_route('recurring-tasks.index')->with('success', 'Recurring task deleted successfully.');
    }

    /**
     * Get the user's categories as select options keyed by UUID.
     *
     * @return Collection<string, string>
     */
    private function categoryOptions(Request $request): Collection
    {
        return $request->user()->categories()->orderBy('name')->pluck('name', 'uuid');
    }

    /**
     * Get the task frequencies as select options keyed by value.
     *
     * @return array<string, string>
     */
    private function frequencyOptions(): array
    {
        return collect(TaskFrequency::cases())
            ->mapWithKeys(fn (TaskFrequency $frequency) => [$frequency->value => $frequency->label()])
            ->all();
    }
}
