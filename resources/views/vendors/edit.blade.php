<x-app-layout title="Ubah Vendor">
    <x-slot name="header">
        <x-page-header :title="'Ubah '.$vendor->name" subtitle="Bengkel, SPBU, atau penyedia kendaraan sewa" :crumbs="['Master Data', 'Vendor', 'Ubah']" />
    </x-slot>


    <x-card class="max-w-3xl">
        @include('vendors.partials.form', ['action' => route('vendors.update', $vendor), 'method' => 'PUT', 'vendor' => $vendor])
    </x-card>
</x-app-layout>
