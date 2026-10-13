@props(['status'])

@if ($status)
    <div
        {{ $attributes->merge(['class' => 'font-medium rounded-md p-3 bg-green-200 text-sm text-green-600 dark:text-green-400 dark:bg-green-100']) }}>
        <span class="px-5">{{ $status }}</span>
    </div>
@endif
