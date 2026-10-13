<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = Validator::make($request->query(), [
            'status' => ['nullable', Rule::in(['complete', 'incomplete'])],
            'category' => ['nullable', 'uuid'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ])->valid();

        $tasks = $request->user()->tasks()
            ->with('category')
            ->filter($filters)
            ->orderByRaw('task_date is null')
            ->latest('task_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks->toResourceCollection()->resolve(),
            'links' => fn () => $tasks->links(),
            'hasPages' => $tasks->hasPages(),
            'filters' => $filters,
            'categories' => $this->categoryOptions($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {

        return view('tasks.create', ['categories' => $this->categoryOptions($request)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $request->user()->tasks()->create($request->taskAttributes());

        return to_route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Task $task): View
    {
        return view('tasks.edit', [
            'task' => $task->load('category')->toResource()->resolve(),
            'categories' => $this->categoryOptions($request),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->taskAttributes());

        return to_route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return to_route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    /**
     * Toggle the completion status of the specified resource.
     */
    public function toggle(Task $task): RedirectResponse
    {
        $task->toggleCompletion();

        return back()->with('success', $task->completed_at ? 'Task marked as complete.' : 'Task marked as incomplete.');
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
}
