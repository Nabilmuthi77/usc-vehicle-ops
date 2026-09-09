<x-app-layout title="Tambah Driver">
    <x-slot name="header">
        <x-page-header title="Tambah Driver" subtitle="Data pengemudi dan masa berlaku SIM" :crumbs="['Master Data', 'Driver', 'Tambah']" />
    </x-slot>


    <x-card>
        @include('drivers.partials.form', ['action' => route('drivers.store'), 'method' => 'POST', 'driver' => null])
    </x-card>
</x-app-layout>
