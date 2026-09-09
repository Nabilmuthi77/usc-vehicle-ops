@props(['options' => [], 'selected' => null, 'containerClass' => 'w-full sm:w-[200px]', 'size' => 'sm'])

<div x-data="{
        open: false,
        value: '{{ addslashes((string) $selected) }}',
        get label() {
            const el = $refs.menu?.querySelector(`[data-value='${this.value}']`);
            return el ? el.innerText.trim() : '{{ addslashes((string) ($options[''] ?? 'Pilih...')) }}';
        }
    }"
    class="relative {{ $containerClass }}"
    @click.outside="open = false">

    <!-- Hidden native select for form submission -->
    <select x-model="value" x-ref="select" {{ $attributes->except('class') }} class="hidden">
        @foreach ($options as $val => $text)
            <option value="{{ $val }}">{{ $text }}</option>
        @endforeach
    </select>

    <!-- Custom trigger -->
    @php
        $buttonClass = $size === 'md'
            ? 'flex h-10 w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 text-sm text-[#02081C] outline-none transition shadow-sm focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10'
            : 'flex h-[34px] w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50';
    @endphp
    <button type="button" @click="open = !open"
        {{ $attributes->only('class')->merge(['class' => $buttonClass]) }}>
        <span x-text="label" class="truncate mr-2" x-bind:class="!value ? 'text-gray-400 font-normal text-sm' : 'font-medium'"></span>
        <x-nav-icon name="chevron-down" class="h-4 w-4 text-gray-400 transition-transform duration-200 shrink-0" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" x-ref="menu"
        x-init="$watch('open', val => {
            if (val) {
                $nextTick(() => {
                    $refs.menu.style.top = 'calc(100% + 8px)';
                    $refs.menu.style.bottom = 'auto';
                    const rect = $refs.menu.getBoundingClientRect();
                    const btnRect = $el.previousElementSibling.getBoundingClientRect();
                    if (rect.bottom > window.innerHeight && btnRect.top > rect.height) {
                        $refs.menu.style.top = 'auto';
                        $refs.menu.style.bottom = 'calc(100% + 8px)';
                    }
                });
            }
        })"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full sm:min-w-max rounded-xl bg-white shadow-lg ring-1 ring-gray-900/5 py-1.5 max-h-60 overflow-y-auto"
        style="display: none; top: calc(100% + 8px);">
        @foreach ($options as $val => $text)
            <button type="button" @click="value = '{{ addslashes((string) $val) }}'; open = false; $nextTick(() => { $refs.select.dispatchEvent(new Event('change', { bubbles: true })) })" data-value="{{ addslashes((string) $val) }}"
                class="flex w-full items-center justify-between px-4 py-2 text-sm text-left transition-colors focus:outline-none"
                x-bind:class="value === '{{ addslashes((string) $val) }}' ? 'bg-usc-50 text-usc-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'">
                <span class="truncate">{{ $text }}</span>
                <span x-show="value === '{{ addslashes((string) $val) }}'" style="display: none;" class="shrink-0 ml-2">
                    <x-nav-icon name="check-circle" class="h-4 w-4 text-usc-600" />
                </span>
            </button>
        @endforeach
    </div>
</div>
