<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Tasks') }}
            </h2>
            @if ($tasks)
                <x-link-button :href="route('tasks.create')">
                    {{ __('New Task') }}
                </x-link-button>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto grid gap-4 sm:px-6 lg:px-8">
            <x-auth-session-status class="px-4 sm:px-0" :status="session('success')" />

            <x-tasks.filters :categories="$categories" :filters="$filters" />

            <div class="bg-white dark:bg-gray-800 shadow-sm overflow-x-auto sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead
                        class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('Task') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Category') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Date') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($tasks as $task)
                            <tr>
                                <td class="px-6 py-4">
                                    <p @class([
                                        'font-medium text-gray-900 dark:text-gray-100',
                                        'line-through text-gray-500 dark:text-gray-400' => $task['is_completed'],
                                    ])>
                                        {{ $task['title'] }}
                                        @if ($task['is_recurring'])
                                            <span class="ms-1 text-xs font-normal text-gray-500 dark:text-gray-400">
                                                ({{ __('Recurring') }})
                                            </span>
                                        @endif
                                    </p>
                                    @if ($task['description'])
                                        <p class="mt-1 max-w-md truncate text-xs text-gray-500 dark:text-gray-400">
                                            {{ $task['description'] }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $task['category']['name'] ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $task['task_date'] ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge :completed="$task['is_completed']" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-4">
                                        <form method="POST" action="{{ route('tasks.toggle', $task['id']) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                class="cursor-pointer text-sm text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800">
                                                {{ $task['is_completed'] ? __('Mark incomplete') : __('Mark complete') }}
                                            </button>
                                        </form>

                                        <a href="{{ route('tasks.edit', $task['id']) }}"
                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                            {{ __('Edit') }}
                                        </a>

                                        <form method="POST" action="{{ route('tasks.destroy', $task['id']) }}"
                                            onsubmit="return confirm('{{ __('Delete this task?') }}');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="cursor-pointer text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                                    {{ __('No tasks found.') }}
                                    <x-text-link :href="route('tasks.create')">{{ __('Create a task') }}</x-text-link>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($hasPages)
                <div class="px-4 sm:px-0">
                    {{ $links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
