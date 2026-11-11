@props(['active'])

@php
    $classes = ($active ?? false)
                ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-emerald-500 text-base font-medium text-emerald-700 bg-emerald-50 focus:outline-none focus:bg-emerald-100 focus:border-emerald-600 transition duration-150 ease-in-out'
                : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-700 hover:text-emerald-700 hover:bg-emerald-50 hover:border-emerald-400 focus:outline-none focus:text-emerald-700 focus:bg-emerald-50 focus:border-emerald-400 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
