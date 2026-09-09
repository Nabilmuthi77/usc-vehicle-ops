<x-app-layout title="Tambah Kontrak Sewa">
    <x-slot name="header">
        <x-page-header title="Tambah Kontrak Sewa" subtitle="Input data kontrak sewa kendaraan baru" :crumbs="['Master Data', 'Vendor', 'Kontrak Sewa']" />
    </x-slot>

    @php
        $vendorOptions = ['' => 'Pilih vendor...'];
        foreach($vendors as $v) {
            $vendorOptions[$v->id] = $v->name;
        }
    @endphp

    <x-card class="w-full">
        <form method="POST" action="{{ route('rental-contracts.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <x-input-label for="contract_number" value="Nomor Kontrak" />
                    <input id="contract_number" name="contract_number" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-gray-50 px-4 text-sm text-[#02081C] font-medium outline-none transition focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                           value="{{ old('contract_number', $newNumber) }}" required readonly />
                    <x-input-error :messages="$errors->get('contract_number')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="vendor_id" value="Vendor / Penyedia Sewa" />
                    <x-filter-select name="vendor_id" id="vendor_id" :options="$vendorOptions" :selected="old('vendor_id')" containerClass="mt-1 w-full" class="border-slate-200" required />
                    <x-input-error :messages="$errors->get('vendor_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="start_date" value="Tanggal Mulai" />
                    <div class="mt-1 w-full">
                        <x-date-picker name="start_date" :value="old('start_date')" />
                    </div>
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="end_date" value="Tanggal Berakhir" />
                    <div class="mt-1 w-full">
                        <x-date-picker name="end_date" :value="old('end_date')" />
                    </div>
                    <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="monthly_cost" value="Biaya Sewa per Bulan (Rp)" />
                    <input id="monthly_cost" name="monthly_cost" type="number" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                           value="{{ old('monthly_cost') }}" required />
                    <x-input-error :messages="$errors->get('monthly_cost')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="reminder_days" value="Pengingat Sebelum Berakhir (Hari)" />
                    <input id="reminder_days" name="reminder_days" type="number" min="1" max="365" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                           value="{{ old('reminder_days', 60) }}" required />
                    <x-input-error :messages="$errors->get('reminder_days')" class="mt-1" />
                </div>

                <div class="sm:col-span-2 lg:col-span-3 mt-4 pt-4 border-t border-gray-100">
                    <h4 class="text-sm font-semibold text-gray-900">Data PIC (Person in Charge)</h4>
                </div>

                <div>
                    <x-input-label for="pic_name" value="Nama PIC" />
                    <input id="pic_name" name="pic_name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('pic_name') }}" />
                    <x-input-error :messages="$errors->get('pic_name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="pic_phone" value="Telepon PIC" />
                    <input id="pic_phone" name="pic_phone" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('pic_phone') }}" />
                    <x-input-error :messages="$errors->get('pic_phone')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="pic_email" value="Email PIC" />
                    <input id="pic_email" name="pic_email" type="email" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                           value="{{ old('pic_email') }}" />
                    <x-input-error :messages="$errors->get('pic_email')" class="mt-1" />
                </div>
            </div>

            <div class="sm:col-span-2 lg:col-span-3 mt-4 pt-4 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">Dokumen & Catatan</h4>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="document" value="Dokumen Kontrak (PDF/JPG)" />
                    <input id="document" name="document" type="file"
                           class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-usc-50 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-usc-700 hover:file:bg-usc-100" />
                    <x-input-error :messages="$errors->get('document')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="notes" value="Catatan" />
                    <textarea id="notes" name="notes" rows="2"
                              class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-gray-100 pt-5 mt-5">
                <a href="{{ route('vendors.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
                <x-btn-primary icon="check-circle" type="submit" class="w-full justify-center sm:w-auto">Simpan Kontrak</x-btn-primary>
            </div>
        </form>
    </x-card>
</x-app-layout>
