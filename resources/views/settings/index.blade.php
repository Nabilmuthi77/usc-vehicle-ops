<x-app-layout title="Pengaturan Sistem">
    <x-slot name="header">
        <x-page-header title="Pengaturan Sistem" subtitle="Identitas perusahaan, ambang notifikasi, dan format dokumen" :crumbs="['Administrasi', 'Pengaturan Sistem']" />
    </x-slot>


    {{-- FR-M1-08 — seluruh pengaturan dikelompokkan per kategori. --}}
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        @foreach ($settings as $group => $items)
            <x-card>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">{{ ucfirst($group) }}</h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($items as $setting)
                        <div>
                            <x-input-label for="setting-{{ $setting->key }}" :value="$setting->label ?? $setting->key" />

                            @if ($setting->type === 'boolean')
                                <select id="setting-{{ $setting->key }}" name="settings[{{ $setting->key }}]"
                                        class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                                    <option value="1" @selected($values[$setting->key] ?? false)>Ya</option>
                                    <option value="0" @selected(! ($values[$setting->key] ?? false))>Tidak</option>
                                </select>
                            @else
                                <input id="setting-{{ $setting->key }}"
                                       type="{{ in_array($setting->type, ['integer', 'decimal'], true) ? 'number' : 'text' }}"
                                       @if ($setting->type === 'decimal') step="0.01" @endif
                                       name="settings[{{ $setting->key }}]"
                                       value="{{ $values[$setting->key] ?? $setting->value }}"
                                       class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                            @endif

                            @if ($setting->description)
                                <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                            @endif
                        </div>
                    @endforeach

                    @if ($group === 'perusahaan')
                        <div>
                            <x-input-label for="company_logo" value="Logo Perusahaan" />
                            <input id="company_logo" type="file" name="company_logo" accept="image/png,image/jpeg"
                                   class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-usc-50 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-usc-700 hover:file:bg-usc-100">
                            <p class="mt-1 text-xs text-gray-500">Maksimal 5 MB, format JPG atau PNG.</p>
                        </div>
                    @endif
                </div>
            </x-card>
        @endforeach

        <div class="flex flex-col sm:flex-row justify-end">
            <x-btn-primary icon="check-circle" type="submit" class="w-full justify-center sm:w-auto">Simpan Perubahan</x-btn-primary>
        </div>
    </form>

    {{-- FR-M3-19 — master harga BBM per jenis beserta riwayat perubahan. --}}
    <x-card class="mt-6">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Master Harga BBM</h3>

        <form method="POST" action="{{ route('settings.fuel-price') }}" class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] items-end">
            @csrf
            <div class="w-full">
                <x-input-label value="Jenis BBM" />
                <x-filter-select name="fuel_type" id="fuel_type" :options="$fuelTypes" containerClass="mt-1 w-full" class="border-slate-200" />
            </div>
            <div class="w-full">
                <x-input-label value="Harga / Liter" />
                <input type="number" step="0.01" name="price_per_liter" required
                       class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
            </div>
            <div class="w-full">
                <x-input-label value="Berlaku Sejak" />
                <div class="mt-1 w-full">
                    <x-date-picker name="effective_date" :value="now()->toDateString()" />
                </div>
            </div>
            <x-btn-primary type="submit" class="w-full lg:w-auto h-[34px] justify-center">
                Simpan Harga
            </x-btn-primary>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-2.5">Jenis BBM</th>
                        <th class="px-4 py-2.5 text-right">Harga Berlaku</th>
                        <th class="px-4 py-2.5">Berlaku Sejak</th>
                        <th class="px-4 py-2.5">Riwayat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($fuelPrices as $type => $prices)
                        @php $current = $prices->first(); @endphp
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">{{ $fuelTypes[$type] ?? $type }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-gray-900">
                                Rp {{ number_format((float) $current->price_per_liter, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $current->effective_date->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $prices->count() }} kali perubahan</td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="4" message="Belum ada data harga BBM." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>

