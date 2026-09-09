<x-guest-layout title="Login">

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <!-- Username -->
        <div>
            <label for="username" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Username') }}
            </label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username Anda"
                autocomplete="username" autofocus
                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">
        </div>

        <!-- Password -->
        <div class="mt-4">
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="block text-sm font-semibold text-[#101828]">
                    {{ __('Password') }}
                </label>
                @if (Route::has('password.request'))
                    <a class="mt-1 text-xs font-semibold text-usc-600 transition hover:text-usc-700 hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <div class="relative">
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda"
                    autocomplete="current-password"
                    class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 pr-24 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">

                <button type="button" data-password-toggle="password"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs font-semibold text-slate-500 transition hover:bg-usc-50 hover:text-usc-600">
                    Lihat
                </button>
            </div>
        </div>

        <!-- Remember Me -->
        <div class="block mt-3">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-usc-600 shadow-sm focus:ring-usc-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-5">
            <button type="submit"
                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-5 text-sm font-semibold text-white shadow-lg shadow-usc-500/20 transition duration-200 hover:-translate-y-0.5 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0">
                {{ __('Log in') }}
            </button>
        </div>

        @if (Route::has('register'))
            <p class="mt-4 text-center text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-usc-600 hover:text-usc-800 transition">
                    Daftar di sini
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
