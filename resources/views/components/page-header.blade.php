@props(['title', 'subtitle' => null, 'crumbs' => []])

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        @if (!empty($crumbs))
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-gray-500">
                @foreach ($crumbs as $crumb)
                    <span class="{{ $loop->last ? 'font-medium text-gray-700' : '' }}">{{ $crumb }}</span>
                    @unless ($loop->last)
                        <span class="text-gray-300">/</span>
                    @endunless
                @endforeach
            </nav>
        @endif
        <h1 class="text-xl font-semibold text-gray-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="mt-3 sm:mt-0 flex w-full sm:w-auto shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
