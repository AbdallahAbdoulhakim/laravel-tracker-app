<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Recurring Tasks') }}
            </h2>
            <div class="flex items-center gap-4">
                <x-text-link :href="route('tasks.index')">
                    {{ __('Back to Tasks') }}
                </x-text-link>
                @if ($recurringTasks)
                    <x-link-button :href="route('recurring-tasks.create')">
                        {{ __('New Recurring Task') }}
                    </x-link-button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto grid gap-4 sm:px-6 lg:px-8">
            <x-auth-session-status class="px-4 sm:px-0" :status="session('success')" />

            <div class="bg-white dark:bg-gray-800 shadow-sm overflow-x-auto sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead
                        class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('Task') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Category') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Frequency') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Start date') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('End date') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recurringTasks as $recurringTask)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $recurringTask['title'] }}
                                    </p>
                                    @if ($recurringTask['description'])
                                        <p class="mt-1 max-w-md truncate text-xs text-gray-500 dark:text-gray-400">
                                            {{ $recurringTask['description'] }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $recurringTask['category']['name'] ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $recurringTask['schedule'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $recurringTask['start_date'] ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ $recurringTask['end_date'] ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-4">
                                        <a href="{{ route('recurring-tasks.edit', $recurringTask['id']) }}"
                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                            {{ __('Edit') }}
                                        </a>

                                        <form method="POST" action="{{ route('recurring-tasks.destroy', $recurringTask['id']) }}"
                                            onsubmit="return confirm('{{ __('Delete this recurring task? Tasks already created from it will be kept.') }}');">
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
                                <td colspan="6" class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                                    {{ __('No recurring tasks found.') }}
                                    <x-text-link :href="route('recurring-tasks.create')">{{ __('Create a recurring task') }}</x-text-link>
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
