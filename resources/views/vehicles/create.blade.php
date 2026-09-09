<x-app-layout title="Tambah Kendaraan">
    <x-slot name="header">
        <x-page-header title="Tambah Kendaraan" subtitle="Jadwal servis otomatis dibuat untuk seluruh jenis servis aktif" :crumbs="['Master Data', 'Kendaraan', 'Tambah']" />
    </x-slot>


    <x-card class="w-full">
        @include('vehicles.partials.form', ['action' => route('vehicles.store'), 'method' => 'POST', 'vehicle' => null])
    </x-card>
</x-app-layout>
