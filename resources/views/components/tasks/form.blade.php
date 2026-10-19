@props(['action', 'categories', 'task' => null])

<form method="POST" action="{{ $action }}" class="grid gap-4">
    @csrf
    @if ($task)
        @method('PUT')
    @endif




    <!-- Title -->
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $task['title'] ?? null)" required
            autofocus maxlength="255" />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <!-- Description -->
    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" maxlength="5000"
            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $task['description'] ?? null) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <!-- Category -->

        <div>
            <x-input-label for="category" :value="__('Category')" />
            <x-select id="category" name="category" class="block mt-1 w-full" :placeholder="__('No category')" :options="$categories"
                :selected="old('category', $task['category']['id'] ?? '-')" />
            <x-input-error :messages="$errors->get('category')" class="mt-2" />
        </div>

        <!-- Date -->

        <div>
            <x-input-label for="task_date" :value="__('Date')" />
            <x-text-input id="task_date" class="block mt-1 w-full" type="date" name="task_date" :value="old(
                'task_date',
                $task ? \Carbon\Carbon::parse($task['task_date'])->format('Y-m-d') : today()->format('Y-m-d'),
            )" />
            <x-input-error :messages="$errors->get('task_date')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <x-text-link :href="route('tasks.index')">{{ __('Cancel') }}</x-text-link>

        <x-primary-button>{{ $slot }}</x-primary-button>
    </div>
</form>
