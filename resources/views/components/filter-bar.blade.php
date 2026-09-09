@props(['searchPlaceholder' => 'Cari...', 'value' => '', 'name' => 'search'])

<div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="relative w-full sm:max-w-sm">
        <x-nav-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        <input
            type="text"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $searchPlaceholder }}"
            class="h-[34px] w-full rounded-xl border border-slate-200 bg-white py-1 pl-9 pr-3 text-sm text-[#02081C] outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
        >
    </div>

    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
        {{ $slot }}
        <div class="flex w-full sm:w-auto gap-2 mt-2 sm:mt-0">
            <button type="submit" class="flex-1 sm:flex-none flex items-center justify-center rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-3 py-1 text-sm font-medium text-white shadow-md shadow-usc-500/20 hover:from-usc-600 hover:to-emerald-600 hover:shadow-lg hover:shadow-usc-500/25 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 h-[34px]">
                Terapkan
            </button>
            <a href="{{ request()->url() }}" title="Reset Filter" class="shrink-0 group inline-flex items-center justify-center rounded-xl bg-white w-[34px] h-[34px] border border-slate-200 hover:border-slate-300 hover:bg-gray-50 hover:text-black hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                <x-nav-icon name="arrow-path" class="h-4 w-4 text-slate-500 group-hover:text-black transition-colors" />
            </a>
        </div>
    </div>
</div>
