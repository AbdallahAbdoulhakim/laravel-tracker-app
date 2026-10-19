<?php

use App\Enums\TaskFrequency;
use App\Models\Category;
use App\Models\RecurringTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests are redirected to the login page', function () {
    $this->get(route('recurring-tasks.index'))->assertRedirect(route('login'));
});

test('the tasks page links to the recurring tasks page and back', function () {
    $this->actingAs($this->user)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee(route('recurring-tasks.index'));

    $this->actingAs($this->user)
        ->get(route('recurring-tasks.index'))
        ->assertOk()
        ->assertSee(route('tasks.index'));
});

test('the index lists only the recurring tasks of the authenticated user', function () {
    RecurringTask::factory()->for($this->user)->weekly(['monday', 'friday'])->create(['title' => 'My own template']);
    RecurringTask::factory()->create(['title' => 'Somebody else template']);

    $this->actingAs($this->user)
        ->get(route('recurring-tasks.index'))
        ->assertOk()
        ->assertSee('My own template')
        ->assertSee('Weekly on Mon, Fri')
        ->assertDontSee('Somebody else template');
});

test('the create and edit forms are displayed', function () {
    $recurringTask = RecurringTask::factory()->for($this->user)->monthly(15)->create(['title' => 'Editable template']);

    $this->actingAs($this->user)->get(route('recurring-tasks.create'))->assertOk();
    $this->actingAs($this->user)
        ->get(route('recurring-tasks.edit', $recurringTask))
        ->assertOk()
        ->assertSee('Editable template');
});

test('a user can create a weekly recurring task in one of their categories', function () {
    $category = Category::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->post(route('recurring-tasks.store'), [
            'title' => 'Team sync',
            'description' => 'Weekly meeting',
            'category' => $category->uuid,
            'frequency' => 'weekly',
            'days' => ['friday', 'monday'],
            'day_of_month' => '12',
            'start_date' => '2026-10-19',
            'end_date' => '2026-12-31',
        ])
        ->assertRedirect(route('recurring-tasks.index'))
        ->assertSessionHas('success');

    $recurringTask = $this->user->recurringTasks()->sole();

    expect($recurringTask)
        ->title->toBe('Team sync')
        ->category_id->toBe($category->id)
        ->frequency->toBe(TaskFrequency::Weekly)
        ->frequency_config->toBe(['days' => ['monday', 'friday']])
        ->and($recurringTask->start_date->toDateString())->toBe('2026-10-19')
        ->and($recurringTask->end_date->toDateString())->toBe('2026-12-31');
});

test('a user can create a monthly recurring task', function () {
    $this->actingAs($this->user)
        ->post(route('recurring-tasks.store'), [
            'title' => 'Pay rent',
            'frequency' => 'monthly',
            'day_of_month' => '31',
            'days' => ['monday'],
        ])
        ->assertRedirect(route('recurring-tasks.index'))
        ->assertSessionHasNoErrors();

    expect($this->user->recurringTasks()->sole())
        ->frequency->toBe(TaskFrequency::Monthly)
        ->frequency_config->toBe(['day' => 31])
        ->start_date->toBeNull()
        ->end_date->toBeNull();
});

test('daily and weekdays recurring tasks have no frequency configuration', function (string $frequency) {
    $this->actingAs($this->user)
        ->post(route('recurring-tasks.store'), ['title' => 'Stand-up', 'frequency' => $frequency, 'days' => ['monday']])
        ->assertSessionHasNoErrors();

    expect($this->user->recurringTasks()->sole()->frequency_config)->toBeNull();
})->with(['daily', 'weekdays']);

test('a recurring task cannot be created with invalid data', function (array $data, array $errors) {
    $this->actingAs($this->user)
        ->post(route('recurring-tasks.store'), ['title' => 'Template', ...$data])
        ->assertSessionHasErrors($errors);

    expect(RecurringTask::count())->toBe(0);
})->with([
    'missing frequency' => [['frequency' => ''], ['frequency']],
    'unknown frequency' => [['frequency' => 'yearly'], ['frequency']],
    'weekly without days' => [['frequency' => 'weekly'], ['days']],
    'weekly with an unknown day' => [['frequency' => 'weekly', 'days' => ['someday']], ['days.0']],
    'monthly without a day' => [['frequency' => 'monthly'], ['day_of_month']],
    'monthly with an out of range day' => [['frequency' => 'monthly', 'day_of_month' => '32'], ['day_of_month']],
    'end date before start date' => [['frequency' => 'daily', 'start_date' => '2026-10-20', 'end_date' => '2026-10-19'], ['end_date']],
    'missing title' => [['title' => '', 'frequency' => 'daily'], ['title']],
]);

test('a recurring task cannot be assigned to the category of another user', function () {
    $foreignCategory = Category::factory()->create();

    $this->actingAs($this->user)
        ->post(route('recurring-tasks.store'), [
            'title' => 'Sneaky template',
            'frequency' => 'daily',
            'category' => $foreignCategory->uuid,
        ])
        ->assertSessionHasErrors('category');

    expect(RecurringTask::count())->toBe(0);
});

test('a user can update their recurring task', function () {
    $recurringTask = RecurringTask::factory()->for($this->user)->weekly(['monday'])->create();

    $this->actingAs($this->user)
        ->put(route('recurring-tasks.update', $recurringTask), [
            'title' => 'Renamed template',
            'frequency' => 'monthly',
            'day_of_month' => '5',
        ])
        ->assertRedirect(route('recurring-tasks.index'));

    expect($recurringTask->refresh())
        ->title->toBe('Renamed template')
        ->frequency->toBe(TaskFrequency::Monthly)
        ->frequency_config->toBe(['day' => 5]);
});

test('a user can delete their recurring task', function () {
    $recurringTask = RecurringTask::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->delete(route('recurring-tasks.destroy', $recurringTask))
        ->assertRedirect(route('recurring-tasks.index'));

    expect($recurringTask->refresh()->trashed())->toBeTrue();
});

test('a user cannot manage the recurring task of another user', function (string $method, string $route) {
    $recurringTask = RecurringTask::factory()->create(['title' => 'Original title']);

    $this->actingAs($this->user)
        ->{$method}(route($route, $recurringTask), ['title' => 'Hijacked', 'frequency' => 'daily'])
        ->assertForbidden();

    expect($recurringTask->refresh())
        ->title->toBe('Original title')
        ->trashed()->toBeFalse();
})->with([
    'edit' => ['get', 'recurring-tasks.edit'],
    'update' => ['put', 'recurring-tasks.update'],
    'destroy' => ['delete', 'recurring-tasks.destroy'],
]);
