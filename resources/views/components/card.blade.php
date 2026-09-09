@props(['title' => null, 'subtitle' => null, 'padded' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-usc-100 bg-gradient-to-br from-usc-50/40 via-white to-white shadow-[0_12px_35px_rgba(16,24,40,0.04)]']) }}>
    @if ($title)
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div>{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div class="{{ $padded ? 'p-5' : '' }}">
        {{ $slot }}
    </div>
</div>


