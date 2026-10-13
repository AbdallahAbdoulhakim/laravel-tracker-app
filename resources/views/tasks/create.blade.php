<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Task') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">

                <x-tasks.form :action="route('tasks.store')" :categories="$categories">
                    {{ __('Create') }}
                </x-tasks.form>
            </div>
        </div>
    </div>
</x-app-layout>
