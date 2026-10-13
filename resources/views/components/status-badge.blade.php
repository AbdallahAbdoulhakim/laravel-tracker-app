@props(['completed' => false])

@php
    $classes = $completed
        ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'
        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $completed ? __('Complete') : __('Incomplete') }}
</span>
