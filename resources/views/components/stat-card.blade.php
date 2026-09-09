@props(['label', 'value', 'icon' => 'chart-bar', 'accent' => 'brand', 'suffix' => null])

@php
    $accents = [
        'brand' => 'bg-usc-50 text-usc-600',
        'good' => 'bg-green-50 text-green-600',
        'warning' => 'bg-amber-50 text-amber-600',
        'critical' => 'bg-red-50 text-red-600',
        'violet' => 'bg-violet-50 text-violet-600',
    ];
@endphp

<div class="rounded-xl border border-usc-100 bg-gradient-to-br from-usc-50/40 via-white to-white shadow-[0_12px_35px_rgba(16,24,40,0.04)] p-3 sm:p-5">
    <div class="flex items-start justify-between gap-1 sm:gap-0">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">{{ $label }}</p>
            <p class="mt-1 sm:mt-2 text-base sm:text-2xl font-semibold text-gray-900 truncate">
                {{ $value }}
                @if ($suffix)
                    <span class="text-xs sm:text-sm font-medium text-gray-400">{{ $suffix }}</span>
                @endif
            </p>
        </div>
        <div class="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg {{ $accents[$accent] ?? $accents['brand'] }}">
            @if ($icon === 'logo')
                <x-application-logo class="h-4 w-4 sm:h-5 sm:w-5" />
            @else
                <x-nav-icon :name="$icon" class="h-4 w-4 sm:h-5 sm:w-5" />
            @endif
        </div>
    </div>
    @isset($footer)
        <div class="mt-3 text-xs text-gray-500">{{ $footer }}</div>
    @endisset
</div>



