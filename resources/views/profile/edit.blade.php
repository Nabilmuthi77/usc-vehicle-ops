<x-app-layout title="Profil Pengguna">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Kelola informasi profil, foto profil, dan kata sandi untuk mengamankan akun Anda.
        </p>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <!-- Kolom Kiri -->
        <div class="space-y-6">
            <!-- Kiri Atas: Ubah Foto Profil -->
            <div class="p-4 sm:p-8 rounded-xl border border-usc-100 bg-gradient-to-br from-usc-50/40 via-white to-white shadow-[0_12px_35px_rgba(16,24,40,0.04)]">
                <div class="w-full">
                    @include('profile.partials.update-profile-photo-form')
                </div>
            </div>

            <!-- Kiri Bawah: Informasi Profil -->
            <div class="p-4 sm:p-8 rounded-xl border border-usc-100 bg-gradient-to-br from-usc-50/40 via-white to-white shadow-[0_12px_35px_rgba(16,24,40,0.04)]">
                <div class="w-full">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <!-- Kolom Kanan -->
        <div class="flex flex-col gap-6">
            <!-- Kanan Atas (Desktop) / Paling Bawah (Mobile): Hapus Akun -->
            <div class="order-last md:order-first p-4 sm:p-8 rounded-xl border border-usc-100 bg-gradient-to-br from-usc-50/40 via-white to-white shadow-[0_12px_35px_rgba(16,24,40,0.04)]">
                <div class="w-full">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <!-- Kanan Bawah (Desktop) / Atasnya Hapus Akun (Mobile): Ubah Password -->
            <div class="order-first md:order-last p-4 sm:p-8 rounded-xl border border-usc-100 bg-gradient-to-br from-usc-50/40 via-white to-white shadow-[0_12px_35px_rgba(16,24,40,0.04)]">
                <div class="w-full">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
