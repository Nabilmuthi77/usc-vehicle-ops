<x-app-layout title="Atur Jadwal Servis">
    <x-slot name="header">
        <x-page-header :title="'Interval Servis — '.$schedule->vehicle?->plate_number"
                       :subtitle="$schedule->serviceType?->name"
                       :crumbs="['Operasional', 'Servis', 'Atur Interval']" />
    </x-slot>


    {{-- FR-M4-01 & FR-M4-02 — interval dapat diatur per unit, KM maupun waktu. --}}
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('service.update-schedule', $schedule) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="interval_km" value="Interval KM" />
                    <x-text-input id="interval_km" name="interval_km" type="number" min="0" class="mt-1 block w-full"
                                  :value="old('interval_km', $schedule->interval_km)" />
                    <x-input-error :messages="$errors->get('interval_km')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="interval_months" value="Interval Waktu (bulan)" />
                    <x-text-input id="interval_months" name="interval_months" type="number" min="0" max="120" class="mt-1 block w-full"
                                  :value="old('interval_months', $schedule->interval_months)" />
                    <p class="mt-1 text-xs text-gray-500">Jatuh tempo dihitung dari yang tercapai lebih dahulu.</p>
                    <x-input-error :messages="$errors->get('interval_months')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="last_service_odometer" value="Odometer Servis Terakhir" />
                    <x-text-input id="last_service_odometer" name="last_service_odometer" type="number" min="0" class="mt-1 block w-full"
                                  :value="old('last_service_odometer', $schedule->last_service_odometer)" />
                    <x-input-error :messages="$errors->get('last_service_odometer')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="last_service_date" value="Tanggal Servis Terakhir" />
                    <x-text-input id="last_service_date" name="last_service_date" type="date" class="mt-1 block w-full"
                                  :value="old('last_service_date', $schedule->last_service_date?->toDateString())" />
                    <x-input-error :messages="$errors->get('last_service_date')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label value="Status Jadwal" />
                <div class="mt-2 flex gap-4">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="is_active" value="1" @checked($schedule->is_active)
                               class="border-gray-300 text-usc-600 focus:ring-usc-400">
                        Aktif
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="is_active" value="0" @checked(! $schedule->is_active)
                               class="border-gray-300 text-usc-600 focus:ring-usc-400">
                        Nonaktif
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <a href="{{ route('service.history', $schedule->vehicle) }}" class="rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
                <x-btn-primary icon="check-circle" type="submit">Simpan &amp; Hitung Ulang</x-btn-primary>
            </div>
        </form>
    </x-card>
</x-app-layout>

