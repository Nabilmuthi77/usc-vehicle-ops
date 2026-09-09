<x-app-layout title="Buat Permintaan Servis">
    <x-slot name="header">
        <x-page-header title="Buat Permintaan Servis ke Vendor" subtitle="Nomor permintaan dibuat otomatis dengan format RSV/{YYYY}/{MM}/{urut}" :crumbs="['Operasional', 'Permintaan Servis', 'Buat']" />
    </x-slot>


    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('service-requests.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="vehicle_id" value="Kendaraan" />
                    @php
                        $vehicleOpts = ['' => '— Pilih kendaraan —'];
                        foreach ($vehicles as $v) {
                            $vehicleOpts[$v->id] = $v->plate_number . ' — ' . $v->full_name . ' (' . $v->ownership->label() . ')';
                        }
                    @endphp
                    <x-filter-select name="vehicle_id" id="vehicle_id" :options="$vehicleOpts"
                                     :selected="old('vehicle_id', $selectedVehicle?->id)"
                                     containerClass="w-full mt-1" class="border-slate-200" required />
                    <x-input-error :messages="$errors->get('vehicle_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="vendor_id" value="Vendor" />
                    @php
                        $vendorOpts = ['' => '— Pilih vendor —'];
                        foreach ($vendors as $vendor) {
                            $vendorOpts[$vendor->id] = $vendor->name;
                        }
                    @endphp
                    <x-filter-select name="vendor_id" id="vendor_id" :options="$vendorOpts"
                                     :selected="old('vendor_id', $selectedVehicle?->rentalContract?->vendor_id)"
                                     containerClass="w-full mt-1" class="border-slate-200" required />
                    <x-input-error :messages="$errors->get('vendor_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="service_type_id" value="Jenis Servis" />
                    @php
                        $serviceTypeOpts = ['' => '— Pemeriksaan umum —'];
                        foreach ($serviceTypes as $type) {
                            $serviceTypeOpts[$type->id] = $type->name;
                        }
                    @endphp
                    <x-filter-select name="service_type_id" id="service_type_id" :options="$serviceTypeOpts"
                                     :selected="old('service_type_id', request('service_type_id', $dueSchedule?->service_type_id))"
                                     containerClass="w-full mt-1" class="border-slate-200" />
                </div>

                <div>
                    <x-input-label for="current_odometer" value="Odometer Saat Ini (km)" />
                    <input id="current_odometer" name="current_odometer" type="number" min="0" 
                           class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('current_odometer', $selectedVehicle?->current_odometer) }}" required />
                    <x-input-error :messages="$errors->get('current_odometer')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="due_odometer" value="KM Jatuh Tempo" />
                    <input id="due_odometer" name="due_odometer" type="number" min="0" 
                           class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('due_odometer', $dueSchedule?->next_due_odometer) }}" />
                    <x-input-error :messages="$errors->get('due_odometer')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="complaint_note" value="Catatan Keluhan" />
                <textarea id="complaint_note" name="complaint_note" rows="3"
                          class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal">{{ old('complaint_note') }}</textarea>
                <x-input-error :messages="$errors->get('complaint_note')" class="mt-1" />
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 border-t border-gray-100 pt-4">
                <a href="{{ route('service-requests.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
                <x-btn-primary icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Buat Permintaan</x-btn-primary>
            </div>
        </form>
    </x-card>
</x-app-layout>

