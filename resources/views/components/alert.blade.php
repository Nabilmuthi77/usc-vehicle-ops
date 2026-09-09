@props(['bag' => 'default'])

{{-- Success Alert --}}

@if (session('success') || session('status'))
    <div data-alert
        class="relative mb-5 flex items-start gap-3 overflow-hidden rounded-2xl border border-emerald-200/80 bg-gradient-to-r from-emerald-50 via-white to-white px-4 pt-2 pb-2 pr-11 text-sm text-slate-700 shadow-sm shadow-emerald-900/5">

        {{-- Accent --}}
        <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-emerald-400 to-emerald-600"></div>

        {{-- Icon --}}
        <div
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100/80 text-emerald-600 ring-1 ring-emerald-200/60">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                <path
                    d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                    stroke="currentColor" stroke-width="1.8" />

                <path d="M8 12.2L10.6 14.8L16 9.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    stroke-linejoin="round" />

            </svg>
        </div>

        {{-- Content --}}
        <div class="min-w-0">

            <p class="font-semibold leading-5 text-slate-800">
                Berhasil
            </p>

            <p class="text-xs leading-tight text-slate-500">
                {{ session('success') ?? session('status') }}
            </p>

        </div>

        {{-- Close --}}
        <button type="button" data-alert-close
            class="absolute right-3 top-3 rounded-lg p-1.5 text-slate-400 transition hover:bg-emerald-100/60 hover:text-emerald-600"
            aria-label="Tutup">

            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />

            </svg>

        </button>

    </div>
@endif


{{-- Error Alert --}}

@php
    $errorBags = [];
    if ($bag === 'all') {
        $hasAnyErrors = collect($errors->getBags())->some(fn($b) => $b->isNotEmpty());
        $errorBags = $errors->getBags();
    } elseif ($bag && $errors->hasBag($bag)) {
        $hasAnyErrors = $errors->getBag($bag)->isNotEmpty();
        if ($hasAnyErrors) {
            $errorBags = [$errors->getBag($bag)];
        }
    } else {
        $hasAnyErrors = false;
    }
@endphp

@if ($hasAnyErrors)

    <div data-alert
        class="relative mb-5 flex items-start gap-3 overflow-hidden rounded-2xl border border-red-200/80 bg-gradient-to-r from-red-50 via-white to-white px-4 pt-2 pb-2 pr-11 text-sm text-slate-700 shadow-sm shadow-red-900/5">

        {{-- Accent --}}
        <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-red-400 to-red-600"></div>

        {{-- Icon --}}
        <div
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100/80 text-red-600 ring-1 ring-red-200/60">

            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                <path
                    d="M10.3 3.8L2.6 17.1C2 18.1 2.7 19.4 3.9 19.4H20.1C21.3 19.4 22 18.1 21.4 17.1L13.7 3.8C13 2.6 11 2.6 10.3 3.8Z"
                    stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />

                <path d="M12 8.5V12.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />

                <path d="M12 16H12.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />

            </svg>

        </div>

        {{-- Content --}}
        <div class="min-w-0">

            <p class="font-semibold leading-5 text-slate-800">
                {{ $title ?? 'Terjadi kesalahan' }}
            </p>

            <ul class="text-xs leading-tight text-slate-500">

                @foreach ($errorBags as $errorBag)
                    @foreach ($errorBag->all() as $error)
                        <li class="pt-0.5">
                            {{ $error }}
                        </li>
                    @endforeach
                @endforeach

            </ul>

        </div>

        {{-- Close --}}
        <button type="button" data-alert-close
            class="absolute right-3 top-3 rounded-lg p-1.5 text-slate-400 transition hover:bg-red-100/60 hover:text-red-600"
            aria-label="Tutup">

            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />

            </svg>

        </button>

    </div>

@endif
