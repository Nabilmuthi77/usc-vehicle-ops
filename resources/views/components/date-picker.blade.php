@props(['name', 'id' => null, 'value' => '', 'placeholder' => 'Pilih tanggal'])

@php
    $id = $id ?? $name;
@endphp

<div data-date-picker {{ $attributes->merge(['class' => 'relative w-full']) }}>

    {{-- Hidden Value --}}
    <input type="hidden" id="{{ $id }}" name="{{ $name }}"
        value="{{ $value }}"
        data-date-value>

    {{-- Date Button --}}
    <button type="button" id="{{ $id }}_display" data-date-button data-placeholder="{{ $placeholder }}"
        class="flex h-[34px] w-full items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 text-left text-sm text-[#02081C] outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
        aria-haspopup="dialog" aria-expanded="false">

        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="1.8" stroke="currentColor"
            class="h-5 w-5 shrink-0 text-usc-500">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25Z" />
        </svg>

        <span data-date-label class="flex-1 text-gray-400 text-sm font-normal">
            {{ $placeholder }}
        </span>

        <x-nav-icon name="chevron-down" class="h-4 w-4 text-gray-400 transition-transform duration-200 shrink-0" data-date-arrow />

    </button>

    {{-- Calendar --}}
    <div data-date-dropdown
        class="absolute left-0 top-[calc(100%+8px)] z-[60] hidden w-[290px] max-w-[calc(100vw-3rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-2xl shadow-slate-900/10 sm:w-[300px]">

        {{-- Calendar Header --}}
        <div data-date-header class="flex items-center justify-between gap-2">
            <button type="button" data-date-prev
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-usc-50 hover:text-usc-600"
                aria-label="Bulan sebelumnya">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                </svg>
            </button>

            <div class="flex items-center gap-1">
                <button type="button" data-date-month-button
                    class="rounded-lg px-2 py-1 text-sm font-bold text-[#02081C] transition hover:bg-usc-50 hover:text-usc-600">
                    <span data-date-month></span>
                </button>
                <button type="button" data-date-year-button
                    class="rounded-lg px-2 py-1 text-sm font-bold text-usc-600 transition hover:bg-usc-50 hover:text-usc-700">
                    <span data-date-year></span>
                </button>
            </div>

            <button type="button" data-date-next
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-usc-50 hover:text-usc-600"
                aria-label="Bulan berikutnya">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                </svg>
            </button>
        </div>

        {{-- Calendar Main --}}
        <div data-date-calendar>
            <div class="mt-4 grid grid-cols-7 text-center">
                @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                    <div class="py-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">
                        {{ $day }}
                    </div>
                @endforeach
            </div>
            <div data-date-days class="grid grid-cols-7 gap-0.5 text-center"></div>
        </div>

        {{-- Year Picker --}}
        <div data-year-picker class="mt-4 hidden">
            <div data-year-grid class="grid grid-cols-4 gap-1.5"></div>
        </div>

        {{-- Month Picker --}}
        <div data-month-picker class="mt-4 hidden">
            <div class="grid grid-cols-3 gap-1.5">
                @foreach ([1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'] as $monthNumber => $monthName)
                    <button type="button" data-month-option="{{ $monthNumber }}"
                        class="rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-usc-50 hover:text-usc-700">
                        {{ $monthName }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Clear Date --}}
        <button type="button" data-date-clear
            class="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 py-2 text-xs font-semibold text-slate-500 transition hover:border-red-100 hover:bg-red-50 hover:text-red-600">
            Hapus tanggal
        </button>

    </div>
</div>
