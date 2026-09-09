{{-- FR-M1-04 — form master driver. --}}
@php
    $userOptions = ['' => '— Tanpa akun sistem —'];
    foreach($users as $user) {
        $userOptions[$user->id] = $user->name . ' (' . $user->email . ')';
    }
@endphp
<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-input-label for="name" value="Nama Driver" />
            <input id="name" name="name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('name', $driver?->name) }}" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="employee_id" value="NIK / Kode Driver" />
            <input id="employee_id" name="employee_id" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('employee_id', $driver?->employee_id) }}" />
            <x-input-error :messages="$errors->get('employee_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="license_number" value="Nomor SIM" />
            <input id="license_number" name="license_number" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('license_number', $driver?->license_number) }}" required />
            <x-input-error :messages="$errors->get('license_number')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="license_type" value="Jenis SIM" />
            <input id="license_type" name="license_type" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('license_type', $driver?->license_type) }}" placeholder="SIM A Umum" required />
            <x-input-error :messages="$errors->get('license_type')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="license_expiry" value="Masa Berlaku SIM" />
            <x-date-picker id="license_expiry" name="license_expiry" :value="old('license_expiry', $driver?->license_expiry?->format('Y-m-d'))" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50" />
            <p class="mt-1 text-[11px] text-gray-500">Driver ber-SIM kedaluwarsa tidak dapat ditugaskan.</p>
            <x-input-error :messages="$errors->get('license_expiry')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="phone" value="Kontak" />
            <input id="phone" name="phone" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
                   value="{{ old('phone', $driver?->phone) }}" />
        </div>
        
        <div>
            <x-input-label for="user_id" value="Akun Pengguna (opsional)" />
            <div class="mt-1">
                <x-filter-select name="user_id" id="user_id" :options="$userOptions" :selected="old('user_id', $driver?->user_id)" containerClass="w-full" class="border-slate-200" />
            </div>
            <p class="mt-1 text-[11px] text-gray-500">Diperlukan agar driver menerima notifikasi.</p>
        </div>

        <div>
            <x-input-label for="notes" value="Catatan" />
            <textarea id="notes" name="notes" rows="1"
                      class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 py-1.5 text-sm text-[#02081C] outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">{{ old('notes', $driver?->notes) }}</textarea>
        </div>

        <div>
            <x-input-label value="Status" />
            <div class="mt-2 flex gap-4 h-[34px] items-center">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="1" @checked(old('is_active', $driver?->is_active ?? true))
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Aktif
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="0" @checked(old('is_active', $driver?->is_active ?? true) == '0')
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Nonaktif
                </label>
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('drivers.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
        <x-btn-primary icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Simpan Driver</x-btn-primary>
    </div>
</form>
