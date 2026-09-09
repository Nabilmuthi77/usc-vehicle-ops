@props(['href' => null, 'icon' => null])

@php
$classes = 'inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-usc-500 to-emerald-500 px-3.5 py-2 text-sm font-medium text-white shadow-lg shadow-usc-500/20 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-usc-500 focus:ring-offset-2 transition-all duration-300';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-nav-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        @if ($icon)<x-nav-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </button>
@endif
