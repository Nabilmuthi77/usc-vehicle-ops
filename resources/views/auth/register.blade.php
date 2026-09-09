<x-guest-layout title="Register">
    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Nama Lengkap') }}
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap Anda"
                autocomplete="name" autofocus
                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">
        </div>

        <!-- Username -->
        <div class="mt-4">
            <label for="username" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Username') }}
            </label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username Anda"
                autocomplete="username"
                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Email') }}
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email Anda"
                autocomplete="email"
                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Password') }}
            </label>

            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda"
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
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password Anda"
                    autocomplete="new-password"
                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 pr-24 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">

                <button type="button" data-password-toggle="password_confirmation"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 transition hover:bg-usc-50 hover:text-usc-600">
                    Lihat
                </button>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit"
                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-5 text-sm font-semibold text-white shadow-lg shadow-usc-500/20 transition duration-200 hover:-translate-y-0.5 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0">
                {{ __('Daftar') }}
            </button>
        </div>

        <p class="mt-4 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-usc-600 hover:text-usc-800 transition">
                Masuk di sini
            </a>
        </p>
    </form>
</x-guest-layout>
