<x-guest-layout title="Reset Password">
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Silakan buat kata sandi baru akun anda di bawah ini.') }}
    </div>

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Akun Tertaut') }}
            </label>
            <div class="flex items-center gap-3 rounded-xl border border-usc-100 bg-usc-50/50 px-4 py-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-usc-100 text-usc-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <div class="text-sm font-medium text-usc-700">
                    {{ old('email', $request->email) }}
                </div>
            </div>
            <!-- Hidden input to submit the email -->
            <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Password') }}
            </label>
            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Masukkan password baru Anda"
                    autocomplete="new-password"
                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 pr-24 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">

                <button type="button" data-password-toggle="password"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 transition hover:bg-usc-50 hover:text-usc-600">
                    Lihat
                </button>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Konfirmasi Password') }}
            </label>
            <div class="relative">
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi password baru Anda"
                    autocomplete="new-password"
                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 pr-24 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">

                <button type="button" data-password-toggle="password_confirmation"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 transition hover:bg-usc-50 hover:text-usc-600">
                    Lihat
                </button>
            </div>
        </div>

        <div class="mt-5">
            <button type="submit"
                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-5 text-sm font-semibold text-white shadow-lg shadow-usc-500/20 transition duration-200 hover:-translate-y-0.5 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
