{{-- FR-M1-02 — form master kendaraan. --}}
@php
    $contractOptions = ['' => '— Tidak ada —'];
    foreach($rentalContracts as $contract) {
        $contractOptions[$contract->id] = $contract->contract_number . ' — ' . $contract->vendor?->name;
    }
    $deptOptions = ['' => '— Tidak ada —'];
    foreach($departments as $dept) {
        $deptOptions[$dept->id] = $dept->name;
    }
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-input-label for="plate_number" value="Nomor Polisi" />
            <input id="plate_number" name="plate_number" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('plate_number', $vehicle?->plate_number) }}" placeholder="B 1234 XI" required />
            <p class="mt-1 text-xs text-gray-500">Digit terakhir dipakai otomatis untuk penyaringan ganjil–genap.</p>
            <x-input-error :messages="$errors->get('plate_number')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="status" value="Status" />
            <x-filter-select name="status" id="status" :options="$statuses" :selected="old('status', $vehicle?->status?->value)" containerClass="mt-1 w-full" class="border-slate-200" required />
            <x-input-error :messages="$errors->get('status')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="brand" value="Merk" />
            <input id="brand" name="brand" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('brand', $vehicle?->brand) }}" required />
            <x-input-error :messages="$errors->get('brand')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="model" value="Tipe" />
            <input id="model" name="model" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('model', $vehicle?->model) }}" required />
            <x-input-error :messages="$errors->get('model')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="year" value="Tahun" />
            <input id="year" name="year" type="number" min="1980" max="{{ date('Y') + 1 }}" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('year', $vehicle?->year) }}" required />
            <x-input-error :messages="$errors->get('year')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="color" value="Warna" />
            <input id="color" name="color" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('color', $vehicle?->color) }}" />
        </div>

        <div>
            <x-input-label for="chassis_number" value="Nomor Rangka" />
            <input id="chassis_number" name="chassis_number" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('chassis_number', $vehicle?->chassis_number) }}" />
        </div>

        <div>
            <x-input-label for="engine_number" value="Nomor Mesin" />
            <input id="engine_number" name="engine_number" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('engine_number', $vehicle?->engine_number) }}" />
        </div>

        <div>
            <x-input-label for="fuel_type" value="Jenis BBM" />
            <x-filter-select name="fuel_type" id="fuel_type" :options="$fuelTypes" :selected="old('fuel_type', $vehicle?->fuel_type?->value)" containerClass="mt-1 w-full" class="border-slate-200" required />
            <x-input-error :messages="$errors->get('fuel_type')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="tank_capacity" value="Kapasitas Tangki (liter)" />
            <input id="tank_capacity" name="tank_capacity" type="number" step="0.01" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('tank_capacity', $vehicle?->tank_capacity) }}" />
            <p class="mt-1 text-xs text-gray-500">Dipakai mendeteksi anomali pengisian melebihi kapasitas.</p>
            <x-input-error :messages="$errors->get('tank_capacity')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="transmission" value="Transmisi" />
            <input id="transmission" name="transmission" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('transmission', $vehicle?->transmission) }}" placeholder="manual / matic" />
        </div>

        <div>
            <x-input-label for="ownership" value="Kepemilikan" />
            <x-filter-select name="ownership" id="ownership" :options="$ownerships" :selected="old('ownership', $vehicle?->ownership?->value)" containerClass="mt-1 w-full" class="border-slate-200" required />
            <x-input-error :messages="$errors->get('ownership')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="rental_contract_id" value="Kontrak Sewa (wajib bila sewa/leasing)" />
            <x-filter-select name="rental_contract_id" id="rental_contract_id" :options="$contractOptions" :selected="old('rental_contract_id', $vehicle?->rental_contract_id)" containerClass="mt-1 w-full" class="border-slate-200" />
            <x-input-error :messages="$errors->get('rental_contract_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="department_id" value="Departemen Pemegang" />
            <x-filter-select name="department_id" id="department_id" :options="$deptOptions" :selected="old('department_id', $vehicle?->department_id)" containerClass="mt-1 w-full" class="border-slate-200" />
        </div>

        <div>
            <x-input-label for="initial_odometer" value="Odometer Awal (km)" />
            <input id="initial_odometer" name="initial_odometer" type="number" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('initial_odometer', $vehicle?->initial_odometer ?? 0) }}" required />
            <x-input-error :messages="$errors->get('initial_odometer')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="current_odometer" value="Odometer Terakhir (km)" />
            <input id="current_odometer" name="current_odometer" type="number" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('current_odometer', $vehicle?->current_odometer ?? 0) }}" required />
            <x-input-error :messages="$errors->get('current_odometer')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="photo" value="Foto Kendaraan" />
            <input id="photo" type="file" name="photo" accept="image/png,image/jpeg"
                   class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-usc-50 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-usc-700 hover:file:bg-usc-100">
            <x-input-error :messages="$errors->get('photo')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="notes" value="Catatan" />
            <textarea id="notes" name="notes" rows="2"
                      class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal">{{ old('notes', $vehicle?->notes) }}</textarea>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('vehicles.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-bg-usc-50 transition duration-300">Batal</a>
        <x-btn-primary icon="check-circle" type="submit" class="w-full justify-center sm:w-auto">Simpan Kendaraan</x-btn-primary>
    </div>
</form>

