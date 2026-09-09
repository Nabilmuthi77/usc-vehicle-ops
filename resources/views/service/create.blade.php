<x-app-layout title="Input Realisasi Servis">
    <x-slot name="header">
        <x-page-header title="Input Realisasi Servis" subtitle="Jadwal servis berikutnya di-generate otomatis setelah disimpan" :crumbs="['Operasional', 'Servis', 'Input']" />
    </x-slot>

    @php
        $vehicleOpts = ['' => '— Pilih kendaraan —'];
        foreach ($vehicles as $v) {
            $vehicleOpts[$v->id] = $v->plate_number . ' — ' . $v->full_name . ' (' . $v->ownership->label() . ')';
        }

        $categoryOpts = [];
        foreach ($categories as $value => $label) {
            $categoryOpts[$value] = $label;
        }

        $serviceTypeOpts = ['' => '— Pilih jenis servis —'];
        foreach ($serviceTypes as $type) {
            $serviceTypeOpts[$type->id] = $type->name;
        }

        $vendorOpts = ['' => '— Pilih vendor —'];
        foreach ($vendors as $vendor) {
            $vendorOpts[$vendor->id] = $vendor->name;
        }

        $costBorneByOpts = ['' => '— Otomatis sesuai kepemilikan —'];
        foreach ($costBorneByOptions as $value => $label) {
            $costBorneByOpts[$value] = $label;
        }
    @endphp

    <x-card class="w-full">
        <form method="POST" action="{{ route('service.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <x-input-label for="vehicle_id" value="Kendaraan" />
                    <div class="mt-1">
                        <x-filter-select name="vehicle_id" id="vehicle_id" :options="$vehicleOpts" :selected="old('vehicle_id', $selectedVehicle?->id)" containerClass="w-full" class="border-slate-200" required />
                    </div>
                    <x-input-error :messages="$errors->get('vehicle_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="category" value="Kategori Servis" />
                    <div class="mt-1">
                        <x-filter-select name="category" id="category" :options="$categoryOpts" :selected="old('category', request('category'))" containerClass="w-full" class="border-slate-200" required />
                    </div>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="service_type_id" value="Jenis Servis (wajib untuk berkala)" />
                    <div class="mt-1">
                        <x-filter-select name="service_type_id" id="service_type_id" :options="$serviceTypeOpts" :selected="old('service_type_id', request('service_type_id'))" containerClass="w-full" class="border-slate-200" />
                    </div>
                    <x-input-error :messages="$errors->get('service_type_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="vendor_id" value="Vendor / Bengkel" />
                    <div class="mt-1">
                        <x-filter-select name="vendor_id" id="vendor_id" :options="$vendorOpts" :selected="old('vendor_id')" containerClass="w-full" class="border-slate-200" />
                    </div>
                    <x-input-error :messages="$errors->get('vendor_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="service_date" value="Tanggal Servis" />
                    <div class="mt-1">
                        <x-date-picker id="service_date" name="service_date" :value="old('service_date', now()->toDateString())" />
                    </div>
                    <x-input-error :messages="$errors->get('service_date')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="odometer" value="Odometer saat Servis (km)" />
                    <input id="odometer" name="odometer" type="number" min="0" 
                           class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('odometer', $selectedVehicle?->current_odometer) }}" required />
                    <x-input-error :messages="$errors->get('odometer')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="invoice_number" value="Nomor Invoice" />
                    <input id="invoice_number" name="invoice_number" type="text" 
                           class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('invoice_number') }}" />
                    <x-input-error :messages="$errors->get('invoice_number')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="total_cost" value="Total Biaya (opsional)" />
                    <input id="total_cost" name="total_cost" type="number" step="0.01" min="0" 
                           class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('total_cost') }}" />
                    <x-input-error :messages="$errors->get('total_cost')" class="mt-1" />
                </div>

                {{-- BR-16 — penanggung biaya mengikuti kepemilikan kendaraan. --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <x-input-label for="cost_borne_by" value="Penanggung Biaya" />
                    <div class="mt-1">
                        <x-filter-select name="cost_borne_by" id="cost_borne_by" :options="$costBorneByOpts" :selected="old('cost_borne_by')" containerClass="w-full" class="border-slate-200" />
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500 leading-tight">
                        Kendaraan sewa/leasing otomatis ditanggung vendor.
                    </p>
                    <x-input-error :messages="$errors->get('cost_borne_by')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="cost_borne_by_reason" value="Alasan Penanggung Biaya (bila diubah)" />
                    <input id="cost_borne_by_reason" name="cost_borne_by_reason" type="text" 
                           class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('cost_borne_by_reason') }}" />
                    <x-input-error :messages="$errors->get('cost_borne_by_reason')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="description" value="Deskripsi Pekerjaan" />
                    <textarea id="description" name="description" rows="2"
                              class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="attachment" value="Lampiran Nota" />
                    <input id="attachment" type="file" name="attachment" accept="image/png,image/jpeg,application/pdf"
                           class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-usc-50 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-usc-700 hover:file:bg-usc-100">
                    <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <a href="{{ route('service.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
                <x-btn-primary icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Simpan Realisasi Servis</x-btn-primary>
            </div>
        </form>
    </x-card>
</x-app-layout>
