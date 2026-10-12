<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Categories') }}
            </h2>
            @if ($categories->isNotEmpty())
                <a href="{{ route('categories.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('New Category') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4 px-4 sm:px-0" :status="session('success')" />

            <div class="bg-white dark:bg-gray-800 shadow-sm overflow-hidden sm:rounded-lg">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($categories as $category)
                        <li class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $category->name }}
                                </p>
                                <p class="mt-1 flex flex-wrap gap-x-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span>
                                        {{ __('Created') }}
                                        <time datetime="{{ $category->created_at?->toIso8601String() }}"
                                            title="{{ $category->created_at?->toDayDateTimeString() }}">
                                            {{ $category->created_at?->diffForHumans() }}
                                        </time>
                                    </span>
                                    <span>
                                        {{ __('Updated') }}
                                        <time datetime="{{ $category->updated_at?->toIso8601String() }}"
                                            title="{{ $category->updated_at?->toDayDateTimeString() }}">
                                            {{ $category->updated_at?->diffForHumans() }}
                                        </time>
                                    </span>
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-4">
                                <a href="{{ route('categories.edit', $category) }}"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    {{ __('Edit') }}
                                </a>

                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                    onsubmit="return confirm('{{ __('Delete this category?') }}');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="cursor-pointer text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('You have no categories yet.') }}
                            </p>
                            <a href="{{ route('categories.create') }}"
                                class="mt-2 inline-block underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Create your first category') }}
                            </a>
                        </li>
                    @endforelse
                </ul>
            </div>

            @if ($categories->hasPages())
                <div class="mt-4 px-4 sm:px-0">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
