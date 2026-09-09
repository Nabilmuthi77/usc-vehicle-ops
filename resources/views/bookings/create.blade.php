<x-app-layout title="Ajukan Peminjaman">
    <x-slot name="header">
        <x-page-header title="Ajukan Peminjaman Kendaraan" subtitle="Kendaraan dan driver ditetapkan Admin GA saat approval" :crumbs="['Operasional', 'Peminjaman', 'Ajukan']" />
    </x-slot>


    {{-- FR-M2-01 — pengajuan hanya berisi 5 field wajib (BR-19). --}}
    <x-card class="w-full">
        <form method="POST" action="{{ route('bookings.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @csrf

            <div class="col-span-1">
                <x-input-label for="booking_date" value="Tanggal Pemakaian" />
                <div class="mt-1">
                    <x-date-picker id="booking_date" name="booking_date" :value="old('booking_date', now()->addDay()->toDateString())" />
                </div>
                <x-input-error :messages="$errors->get('booking_date')" class="mt-1" />
            </div>

            <div class="col-span-1">
                <x-input-label value="Tujuan" />
                <div class="mt-1">
                    <x-region-picker name="destination" :value="old('destination')" />
                </div>
                <x-input-error :messages="$errors->get('destination')" class="mt-1" />
            </div>

            <div class="col-span-1">
                <x-input-label for="duration_type" value="Durasi" />
                <div class="mt-1">
                    <x-filter-select name="duration_type" id="duration_type" :options="$durations" :selected="old('duration_type')" containerClass="w-full" class="border-slate-200" required />
                </div>
                <x-input-error :messages="$errors->get('duration_type')" class="mt-1" />
            </div>

            <div class="col-span-1">
                <x-input-label for="purpose" value="Keperluan" />
                <textarea id="purpose" name="purpose" rows="2" required
                          class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                          placeholder="Tuliskan Alasan atau kegiatan yang perlu anda lakukan">{{ old('purpose') }}</textarea>
                <x-input-error :messages="$errors->get('purpose')" class="mt-1" />
            </div>

            <div class="col-span-1">
                <x-input-label for="additional_note" value="Catatan Tambahan (opsional)" />
                <textarea id="additional_note" name="additional_note" rows="2"
                          class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                          placeholder="Jumlah penumpang, jam berangkat, dsb">{{ old('additional_note') }}</textarea>
                <x-input-error :messages="$errors->get('additional_note')" class="mt-1" />
            </div>

            <div class="col-span-1">
                <x-input-label value="Melewati area ganjil–genap?" />
                <div class="mt-1 flex h-[34px] items-center gap-6">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="odd_even_zone" value="1" @checked(old('odd_even_zone') == '1')
                               class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                        Ya
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="odd_even_zone" value="0" @checked(old('odd_even_zone', '0') == '0')
                               class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                        Tidak
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Bila Ya, sistem akan batasi pilihan kendaraan sesuai paritas pelat.
                </p>
                <x-input-error :messages="$errors->get('odd_even_zone')" class="mt-1" />
            </div>

            <div class="col-span-1 md:col-span-2 lg:col-span-3 flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 mt-4">
                <a href="{{ route('bookings.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
                <x-btn-primary icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Kirim Pengajuan</x-btn-primary>
            </div>
        </form>
    </x-card>
</x-app-layout>

