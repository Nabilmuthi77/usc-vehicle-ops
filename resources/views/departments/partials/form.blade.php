{{-- FR-M1-05 — form departemen / cost center. --}}
<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-input-label for="code" value="Kode Departemen" />
            <input id="code" name="code" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('code', $department?->code) }}" required />
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Departemen" />
            <input id="name" name="name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('name', $department?->name) }}" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="pic_name" value="Nama PIC" />
            <input id="pic_name" name="pic_name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('pic_name', $department?->pic_name) }}" />
        </div>

        <div>
            <x-input-label for="pic_phone" value="Kontak PIC" />
            <input id="pic_phone" name="pic_phone" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('pic_phone', $department?->pic_phone) }}" />
        </div>

        <div>
            <x-input-label for="description" value="Keterangan" />
            <textarea id="description" name="description" rows="1"
                      class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 py-1.5 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">{{ old('description', $department?->description) }}</textarea>
        </div>

        <div>
            <x-input-label value="Status" />
            <div class="mt-2 flex gap-4 h-[34px] items-center">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="1" @checked(old('is_active', $department?->is_active ?? true))
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Aktif
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="0" @checked(old('is_active', $department?->is_active ?? true) == '0')
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Nonaktif
                </label>
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('departments.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
        <x-btn-primary icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Simpan Departemen</x-btn-primary>
    </div>
</form>
