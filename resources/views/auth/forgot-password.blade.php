<x-guest-layout title="Lupa Password">
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Lupa kata sandi Anda? Tidak masalah.') }}<br>
        {{ __('Cukup beri tahu kami alamat email Anda, dan kami akan mengirimkan tautan pengaturan ulang kata sandi melalui email agar Anda dapat membuat kata sandi baru.') }}
    </div>


    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-[#101828]">
                {{ __('Email') }}
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email Anda"
                autocomplete="email" autofocus
                class="h-10 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-[#101828] outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10">
        </div>

        <div class="mt-5 flex items-center justify-between">
            <a href="{{ route('login') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
            </a>

            <button type="submit"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-5 text-sm font-semibold text-white shadow-lg shadow-usc-500/20 transition duration-200 hover:-translate-y-0.5 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
