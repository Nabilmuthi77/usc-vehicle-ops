{{-- FR-M1-06 — form vendor bengkel / SPBU / penyedia sewa. --}}
<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-input-label for="name" value="Nama Vendor" />
            <input id="name" name="name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('name', $vendor?->name) }}" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="type" value="Jenis Vendor" />
            <x-filter-select name="type" id="type" :options="$types" :selected="old('type', $vendor?->type?->value)" containerClass="mt-1 w-full" class="border-slate-200" required />
            <x-input-error :messages="$errors->get('type')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="pic_name" value="Nama PIC" />
            <input id="pic_name" name="pic_name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('pic_name', $vendor?->pic_name) }}" />
        </div>

        <div>
            <x-input-label for="pic_phone" value="Telepon PIC" />
            <input id="pic_phone" name="pic_phone" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('pic_phone', $vendor?->pic_phone) }}" />
        </div>

        <div>
            <x-input-label for="pic_email" value="Email PIC" />
            <input id="pic_email" name="pic_email" type="email" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('pic_email', $vendor?->pic_email) }}" />
            <p class="mt-1 text-xs text-gray-500">Dipakai untuk mengirim permintaan servis langsung dari sistem.</p>
            <x-input-error :messages="$errors->get('pic_email')" class="mt-1" />
        </div>

        <div>
            <x-input-label value="Status" />
            <div class="mt-2 flex gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="1" @checked(old('is_active', $vendor?->is_active ?? true))
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Aktif
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="0" @checked(old('is_active', $vendor?->is_active ?? true) == '0')
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Nonaktif
                </label>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="address" value="Alamat" />
            <textarea id="address" name="address" rows="2"
                      class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal">{{ old('address', $vendor?->address) }}</textarea>
        </div>

        <div>
            <x-input-label for="notes" value="Catatan" />
            <textarea id="notes" name="notes" rows="2"
                      class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal">{{ old('notes', $vendor?->notes) }}</textarea>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('vendors.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
        <x-btn-primary icon="check-circle" type="submit" class="w-full justify-center sm:w-auto">Simpan Vendor</x-btn-primary>
    </div>
</form>

