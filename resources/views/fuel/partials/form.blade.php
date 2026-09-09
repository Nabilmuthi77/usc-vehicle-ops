{{-- FR-M3-01 s.d. FR-M3-08 — form transaksi pengisian BBM. --}}
@php
    $vehicleOptions = ['' => '— Pilih kendaraan —'];
    foreach($vehicles as $v) {
        $vehicleOptions[$v->id] = $v->plate_number . ' — ' . $v->full_name;
    }

    $driverOptions = ['' => '— Tidak ada —'];
    foreach($drivers as $d) {
        $driverOptions[$d->id] = $d->name;
    }

    $bookingOptions = ['' => '— Tidak dikaitkan —'];
    foreach($bookings as $b) {
        $bookingOptions[$b->id] = $b->booking_number . ' — ' . $b->booking_date->format('d-m-Y');
    }
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-input-label for="vehicle_id" value="Kendaraan" />
            <div class="mt-1">
                <x-filter-select name="vehicle_id" id="vehicle_id" :options="$vehicleOptions" :selected="old('vehicle_id', $transaction?->vehicle_id)" containerClass="w-full" class="border-slate-200" required />
            </div>
            <x-input-error :messages="$errors->get('vehicle_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="driver_id" value="Driver (opsional)" />
            <div class="mt-1">
                <x-filter-select name="driver_id" id="driver_id" :options="$driverOptions" :selected="old('driver_id', $transaction?->driver_id)" containerClass="w-full" class="border-slate-200" />
            </div>
        </div>

        <div>
            <x-input-label for="transaction_datetime" value="Tanggal & Jam Pengisian" />
            <div x-data="{
                    date: '{{ old('transaction_date_ignore', $transaction?->transaction_datetime?->format('Y-m-d') ?? now()->format('Y-m-d')) }}',
                    time: '{{ old('transaction_time_ignore', $transaction?->transaction_datetime?->format('H:i') ?? now()->format('H:i')) }}'
                 }"
                 @change="
                    if ($event.target.id === 'transaction_date') date = $event.target.value;
                    if ($event.target.id === 'transaction_time') time = $event.target.value;
                 "
                 class="mt-1 flex gap-2">
                 
                <input type="hidden" name="transaction_datetime" x-bind:value="date && time ? date + 'T' + time : ''">

                <div class="w-[75%] sm:w-[80%]">
                    <x-date-picker id="transaction_date" name="transaction_date_ignore" :value="old('transaction_date_ignore', $transaction?->transaction_datetime?->format('Y-m-d') ?? now()->format('Y-m-d'))" />
                </div>
                
                <div class="w-[25%] sm:w-[20%]">
                    <x-time-picker id="transaction_time" name="transaction_time_ignore" :value="old('transaction_time_ignore', $transaction?->transaction_datetime?->format('H:i') ?? now()->format('H:i'))" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('transaction_datetime')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="station_name" value="SPBU" />
            <input id="station_name" name="station_name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('station_name', $transaction?->station_name) }}" required />
            <x-input-error :messages="$errors->get('station_name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="fuel_type" value="Jenis BBM" />
            <div class="mt-1">
                <x-filter-select name="fuel_type" id="fuel_type" :options="$fuelTypes" :selected="old('fuel_type', $transaction?->fuel_type?->value)" containerClass="w-full" class="border-slate-200" required />
            </div>
            <x-input-error :messages="$errors->get('fuel_type')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="payment_method" value="Metode Bayar" />
            <div class="mt-1">
                <x-filter-select name="payment_method" id="payment_method" :options="$paymentMethods" :selected="old('payment_method', $transaction?->payment_method?->value)" containerClass="w-full" class="border-slate-200" required />
            </div>
        </div>

        <div>
            <x-input-label for="liters" value="Jumlah Liter" />
            <input id="liters" name="liters" type="number" step="0.01" min="0.01" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('liters', $transaction?->liters) }}" required />
            <x-input-error :messages="$errors->get('liters')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="price_per_liter" value="Harga per Liter" />
            <input id="price_per_liter" name="price_per_liter" type="number" step="0.01" min="1" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('price_per_liter', $transaction?->price_per_liter) }}" required />
            <x-input-error :messages="$errors->get('price_per_liter')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="total_cost" value="Total Biaya (kosongkan untuk hitung otomatis)" />
            <input id="total_cost" name="total_cost" type="number" step="0.01" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('total_cost', $transaction?->total_cost) }}" />
            <x-input-error :messages="$errors->get('total_cost')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="odometer" value="Odometer saat Pengisian (km)" />
            <input id="odometer" name="odometer" type="number" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('odometer', $transaction?->odometer) }}" required />
            <x-input-error :messages="$errors->get('odometer')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="receipt_number" value="Nomor Nota" />
            <input id="receipt_number" name="receipt_number" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('receipt_number', $transaction?->receipt_number) }}" required />
            <x-input-error :messages="$errors->get('receipt_number')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="booking_id" value="Kaitkan ke Peminjaman (opsional)" />
            <div class="mt-1">
                <x-filter-select name="booking_id" id="booking_id" :options="$bookingOptions" :selected="old('booking_id', $transaction?->booking_id)" containerClass="w-full" class="border-slate-200" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {{-- FR-M3-03 — penanda pengisian penuh vs sebagian (dasar perhitungan BR-05). --}}
    <div>
        <x-input-label value="Jenis Pengisian" />
        <div class="mt-2 flex gap-4">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="is_full_tank" value="1"
                       @checked(old('is_full_tank', $transaction?->is_full_tank ?? true))
                       class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                Penuh (full tank)
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="is_full_tank" value="0"
                       @checked(old('is_full_tank', $transaction?->is_full_tank) === false || old('is_full_tank') === '0')
                       class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                Sebagian (partial)
            </label>
        </div>
        <p class="mt-1 text-xs text-gray-500">
            Konsumsi km/L hanya dihitung pada pengisian penuh (metode full-to-full).
        </p>
    </div>

    {{-- FR-M3-07 — nota wajib difoto. --}}
    <div>
        <x-input-label for="receipt_photo" value="Foto Nota" />
        <input id="receipt_photo" type="file" name="receipt_photo" accept="image/png,image/jpeg,application/pdf"
               class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-usc-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-usc-700 hover:file:bg-usc-100" @required($transaction === null)>
        <p class="mt-1 text-xs text-gray-500">Maksimal 5 MB. Klaim tanpa lampiran nota akan ditolak sistem.</p>
            <x-input-error :messages="$errors->get('receipt_photo')" class="mt-1" />
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('fuel.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
        <x-btn-primary icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Simpan Transaksi</x-btn-primary>
    </div>
</form>

