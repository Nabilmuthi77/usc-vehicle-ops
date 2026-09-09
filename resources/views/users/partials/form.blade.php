{{-- FR-M1-07 — form pengguna & penetapan peran. --}}
@php
    $deptOptions = ['' => '— Tidak ada —'];
    foreach($departments as $dept) {
        $deptOptions[$dept->id] = $dept->name;
    }
    
    $roleOptions = [];
    foreach($roles as $role) {
        $roleOptions[$role->name] = $role->name;
    }
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-input-label for="name" value="Nama" />
            <input id="name" name="name" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('name', $user?->name) }}" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="username" value="Username" />
            <input id="username" name="username" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('username', $user?->username) }}" />
            <p class="mt-1 text-xs text-gray-500">Dapat dipakai untuk login selain email.</p>
            <x-input-error :messages="$errors->get('username')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <input id="email" name="email" type="email" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('email', $user?->email) }}" required />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="phone" value="Kontak" />
            <input id="phone" name="phone" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                   value="{{ old('phone', $user?->phone) }}" />
        </div>

        <div>
            <x-input-label for="department_id" value="Departemen" />
            <x-filter-select name="department_id" id="department_id" :options="$deptOptions" :selected="old('department_id', $user?->department_id)" containerClass="mt-1 w-full" class="border-slate-200" />
        </div>

        <div>
            <x-input-label for="role" value="Peran" />
            <x-filter-select name="role" id="role" :options="$roleOptions" :selected="old('role', $user?->roles->first()?->name)" containerClass="mt-1 w-full" class="border-slate-200" required />
            <x-input-error :messages="$errors->get('role')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" :value="$user ? 'Kata Sandi Baru (kosongkan bila tidak diubah)' : 'Kata Sandi'" />
            <input id="password" name="password" type="password" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" {{ ! $user ? 'required' : '' }} />
            <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter, mengandung huruf dan angka.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" {{ ! $user ? 'required' : '' }} />
        </div>
        <div>
            <x-input-label value="Status Akun" />
            <div class="mt-2 flex gap-4 h-[34px] items-center">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true))
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Aktif
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_active" value="0" @checked(old('is_active', $user?->is_active ?? true) == '0')
                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                    Nonaktif
                </label>
            </div>
            <p class="mt-1 text-xs text-gray-500">Akun nonaktif tidak dapat mengakses sistem.</p>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-gray-100 pt-4">
        <a href="{{ route('users.index') }}" class="w-full sm:w-auto text-center rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300">Batal</a>
        <x-btn-primary icon="check-circle" type="submit" class="w-full justify-center sm:w-auto">Simpan Pengguna</x-btn-primary>
    </div>
</form>

