<x-app-layout :title="$title">
    <x-slot name="header">
        <x-page-header :title="$title" :subtitle="$subtitle ?? null" :crumbs="$crumbs ?? []" />
    </x-slot>

    <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-white py-20 text-center">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-usc-50 text-usc-500">
            <x-nav-icon name="construction" class="h-7 w-7" />
        </div>
        <h2 class="mt-4 text-base font-semibold text-gray-900">Modul {{ $title }} segera hadir</h2>
        <p class="mt-1 max-w-sm text-sm text-gray-500">
            Halaman ini akan dibangun pada tahap berikutnya sesuai roadmap di PRD. Tampilan yang sudah tersedia saat ini: Dashboard, Peminjaman, BBM, Servis, Kendaraan, dan Tol.
        </p>
        <a href="{{ route('dashboard') }}" class="mt-5 inline-flex items-center gap-1.5 rounded-lg bg-usc-600 px-4 py-2 text-sm font-medium text-white hover:bg-usc-700">
            Kembali ke Dashboard
        </a>
    </div>
</x-app-layout>
