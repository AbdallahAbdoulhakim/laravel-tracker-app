@props(['action', 'categories', 'frequencies', 'recurringTask' => null])

@php
    $frequency = old('frequency', $recurringTask['frequency'] ?? \App\Enums\TaskFrequency::Daily->value);
    $selectedDays = old('days', $recurringTask['days'] ?? []);
    $startDate = $recurringTask['start_date'] ?? null;
    $endDate = $recurringTask['end_date'] ?? null;
@endphp

<form method="POST" action="{{ $action }}" class="grid gap-4" x-data="{ frequency: @js($frequency) }">
    @csrf
    @if ($recurringTask)
        @method('PUT')
    @endif

    <!-- Title -->
    <div>
        <x-input-label for="title" :value="__('Title')" />
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $recurringTask['title'] ?? null)" required
            autofocus maxlength="255" />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <!-- Description -->
    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" maxlength="255"
            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $recurringTask['description'] ?? null) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <!-- Category -->
        <div>
            <x-input-label for="category" :value="__('Category')" />
            <x-select id="category" name="category" class="block mt-1 w-full" :placeholder="__('No category')" :options="$categories"
                :selected="old('category', $recurringTask['category']['id'] ?? null)" />
            <x-input-error :messages="$errors->get('category')" class="mt-2" />
        </div>

        <!-- Frequency -->
        <div>
            <x-input-label for="frequency" :value="__('Frequency')" />
            <x-select id="frequency" name="frequency" class="block mt-1 w-full" :options="$frequencies" :selected="$frequency"
                x-model="frequency" required />
            <x-input-error :messages="$errors->get('frequency')" class="mt-2" />
        </div>
    </div>

    <!-- Weekly days -->
    <fieldset x-show="frequency === @js(\App\Enums\TaskFrequency::Weekly->value)" @style(['display: none' => $frequency !== \App\Enums\TaskFrequency::Weekly->value])>
        <legend class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('Repeat on') }}</legend>
        <div class="mt-2 flex flex-wrap gap-4">
            @foreach (\App\Models\RecurringTask::WEEKDAYS as $day => $label)
                <label for="day_{{ $day }}" class="inline-flex items-center">
                    <input id="day_{{ $day }}" type="checkbox" name="days[]" value="{{ $day }}"
                        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                        @checked(in_array($day, (array) $selectedDays, true))>
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __($label) }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="[...$errors->get('days'), ...$errors->get('days.*')]" class="mt-2" />
    </fieldset>

    <!-- Monthly day -->
    <div x-show="frequency === @js(\App\Enums\TaskFrequency::Monthly->value)" @style(['display: none' => $frequency !== \App\Enums\TaskFrequency::Monthly->value])>
        <x-input-label for="day_of_month" :value="__('Day of month')" />
        <x-text-input id="day_of_month" class="block mt-1 w-full sm:w-1/2" type="number" name="day_of_month" min="1" max="31"
            :value="old('day_of_month', $recurringTask['day_of_month'] ?? null)" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ __('In shorter months, the task is created on the last day of the month.') }}
        </p>
        <x-input-error :messages="$errors->get('day_of_month')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <!-- Start date -->
        <div>
            <x-input-label for="start_date" :value="__('Start date (optional)')" />
            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old(
                'start_date',
                $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : null,
            )" />
            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
        </div>

        <!-- End date -->
        <div>
            <x-input-label for="end_date" :value="__('End date (optional)')" />
            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old(
                'end_date',
                $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : null,
            )" />
            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <x-text-link :href="route('recurring-tasks.index')">{{ __('Cancel') }}</x-text-link>

        <x-primary-button>{{ $slot }}</x-primary-button>
    </div>
</form>
