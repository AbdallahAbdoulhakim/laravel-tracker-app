<?php

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests are redirected to the login page', function () {
    $this->get(route('tasks.index'))->assertRedirect(route('login'));
});

test('the index lists only the tasks of the authenticated user', function () {
    Task::factory()->for($this->user)->create(['title' => 'My own task']);
    Task::factory()->create(['title' => 'Somebody else task']);

    $this->actingAs($this->user)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('My own task')
        ->assertDontSee('Somebody else task');
});

test('the index can be filtered by completion status', function (string $status, string $visible, string $hidden) {
    Task::factory()->for($this->user)->completed()->create(['title' => 'Finished task']);
    Task::factory()->for($this->user)->create(['title' => 'Pending task']);

    $this->actingAs($this->user)
        ->get(route('tasks.index', ['status' => $status]))
        ->assertOk()
        ->assertSee($visible)
        ->assertDontSee($hidden);
})->with([
    'complete' => ['complete', 'Finished task', 'Pending task'],
    'incomplete' => ['incomplete', 'Pending task', 'Finished task'],
]);

test('the index can be filtered by category', function () {
    $category = Category::factory()->for($this->user)->create();
    Task::factory()->for($this->user)->for($category)->create(['title' => 'Categorised task']);
    Task::factory()->for($this->user)->create(['title' => 'Loose task']);

    $this->actingAs($this->user)
        ->get(route('tasks.index', ['category' => $category->uuid]))
        ->assertOk()
        ->assertSee('Categorised task')
        ->assertDontSee('Loose task');
});

test('the index can be filtered by date range', function () {
    Task::factory()->for($this->user)->create(['title' => 'Early task', 'task_date' => '2026-10-01']);
    Task::factory()->for($this->user)->create(['title' => 'Middle task', 'task_date' => '2026-10-10']);
    Task::factory()->for($this->user)->create(['title' => 'Late task', 'task_date' => '2026-10-20']);

    $this->actingAs($this->user)
        ->get(route('tasks.index', ['from' => '2026-10-05', 'to' => '2026-10-10']))
        ->assertOk()
        ->assertSee('Middle task')
        ->assertDontSee('Early task')
        ->assertDontSee('Late task');
});

test('invalid filters are ignored', function () {
    Task::factory()->for($this->user)->create(['title' => 'Visible task']);

    $this->actingAs($this->user)
        ->get(route('tasks.index', ['status' => 'bogus', 'category' => 'not-a-uuid', 'from' => 'yesterday', 'to' => ['x']]))
        ->assertOk()
        ->assertSee('Visible task');
});

test('the create and edit forms are displayed', function () {
    $task = Task::factory()->for($this->user)->create(['title' => 'Editable task']);

    $this->actingAs($this->user)->get(route('tasks.create'))->assertOk();
    $this->actingAs($this->user)->get(route('tasks.edit', $task))->assertOk()->assertSee('Editable task');
});

test('a user can create a task in one of their categories', function () {
    $category = Category::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->post(route('tasks.store'), [
            'title' => 'Write tests',
            'description' => 'Cover the task feature',
            'category' => $category->uuid,
            'task_date' => '2026-10-13',
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success');

    $task = $this->user->tasks()->sole();

    expect($task->title)->toBe('Write tests')
        ->and($task->category_id)->toBe($category->id)
        ->and($task->task_date->toDateString())->toBe('2026-10-13')
        ->and($task->completed_at)->toBeNull();
});

test('a task can be created without a date', function () {
    $this->actingAs($this->user)
        ->post(route('tasks.store'), ['title' => 'Someday task', 'task_date' => ''])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHasNoErrors();

    expect($this->user->tasks()->sole()->task_date)->toBeNull();
});

test('the date of a task can be cleared', function () {
    $task = Task::factory()->for($this->user)->create(['task_date' => '2026-10-13']);

    $this->actingAs($this->user)
        ->put(route('tasks.update', $task), ['title' => $task->title, 'task_date' => ''])
        ->assertRedirect(route('tasks.index'));

    expect($task->refresh()->task_date)->toBeNull();
});

test('undated tasks are listed after dated tasks and can be edited', function () {
    $undated = Task::factory()->for($this->user)->create(['title' => 'Undated task', 'task_date' => null]);
    Task::factory()->for($this->user)->create(['title' => 'Dated task', 'task_date' => '2026-10-13']);

    $this->actingAs($this->user)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSeeInOrder(['Dated task', 'Undated task']);

    $this->actingAs($this->user)->get(route('tasks.edit', $undated))->assertOk();
});

test('a task cannot be created with invalid data', function () {
    $this->actingAs($this->user)
        ->post(route('tasks.store'), ['title' => '', 'task_date' => 'tomorrow'])
        ->assertSessionHasErrors(['title', 'task_date']);

    expect(Task::count())->toBe(0);
});

test('a task cannot be assigned to the category of another user', function () {
    $foreignCategory = Category::factory()->create();

    $this->actingAs($this->user)
        ->post(route('tasks.store'), [
            'title' => 'Sneaky task',
            'category' => $foreignCategory->uuid,
            'task_date' => '2026-10-13',
        ])
        ->assertSessionHasErrors('category');

    expect(Task::count())->toBe(0);
});

test('a user can update their task', function () {
    $category = Category::factory()->for($this->user)->create();
    $task = Task::factory()->for($this->user)->for($category)->create();

    $this->actingAs($this->user)
        ->put(route('tasks.update', $task), ['title' => 'Renamed task', 'task_date' => '2026-11-01'])
        ->assertRedirect(route('tasks.index'));

    expect($task->refresh()->title)->toBe('Renamed task')
        ->and($task->category_id)->toBeNull()
        ->and($task->task_date->toDateString())->toBe('2026-11-01');
});

test('a user can toggle the completion status of their task', function () {
    $task = Task::factory()->for($this->user)->create();

    $this->actingAs($this->user)->patch(route('tasks.toggle', $task))->assertRedirect();
    expect($task->refresh()->completed_at)->not->toBeNull();

    $this->actingAs($this->user)->patch(route('tasks.toggle', $task))->assertRedirect();
    expect($task->refresh()->completed_at)->toBeNull();
});

test('a user can delete their task', function () {
    $task = Task::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'));

    expect($task->fresh())->toBeNull();
});

test('a user cannot manage the task of another user', function (string $method, string $route) {
    $task = Task::factory()->create(['title' => 'Original title']);

    $this->actingAs($this->user)
        ->{$method}(route($route, $task), ['title' => 'Hijacked', 'task_date' => '2026-10-13'])
        ->assertForbidden();

    expect($task->refresh())
        ->title->toBe('Original title')
        ->completed_at->toBeNull();
})->with([
    'edit' => ['get', 'tasks.edit'],
    'update' => ['put', 'tasks.update'],
    'toggle' => ['patch', 'tasks.toggle'],
    'destroy' => ['delete', 'tasks.destroy'],
]);
