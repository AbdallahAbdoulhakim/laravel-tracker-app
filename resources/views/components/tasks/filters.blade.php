@props(['categories', 'filters' => []])

<form method="GET" action="{{ route('tasks.index') }}"
    {{ $attributes->merge(['class' => 'grid items-end gap-4 p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg sm:grid-cols-2 lg:grid-cols-5']) }}>
    <div>
        <x-input-label for="status" :value="__('Status')" />
        <x-select id="status" name="status" class="block mt-1 w-full" :placeholder="__('All statuses')"
            :options="['incomplete' => __('Incomplete'), 'complete' => __('Complete')]" :selected="$filters['status'] ?? null" />
    </div>

    <div>
        <x-input-label for="category" :value="__('Category')" />
        <x-select id="category" name="category" class="block mt-1 w-full" :placeholder="__('All categories')"
            :options="$categories" :selected="$filters['category'] ?? null" />
    </div>

    <div>
        <x-input-label for="from" :value="__('From')" />
        <x-text-input id="from" name="from" type="date" class="block mt-1 w-full" :value="$filters['from'] ?? null" />
    </div>

    <div>
        <x-input-label for="to" :value="__('To')" />
        <x-text-input id="to" name="to" type="date" class="block mt-1 w-full" :value="$filters['to'] ?? null" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Filter') }}</x-primary-button>
        <x-text-link :href="route('tasks.index')">{{ __('Reset') }}</x-text-link>
    </div>
</form>
