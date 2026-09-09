<x-app-layout title="Tambah Vendor">
    <x-slot name="header">
        <x-page-header title="Tambah Vendor" subtitle="Bengkel, SPBU, atau penyedia kendaraan sewa" :crumbs="['Master Data', 'Vendor', 'Tambah']" />
    </x-slot>


    <x-card class="w-full">
        @include('vendors.partials.form', ['action' => route('vendors.store'), 'method' => 'POST', 'vendor' => null])
    </x-card>
</x-app-layout>
