<x-app-layout title="Ubah Driver">
    <x-slot name="header">
        <x-page-header :title="'Ubah '.$driver->name" subtitle="Data pengemudi dan masa berlaku SIM" :crumbs="['Master Data', 'Driver', 'Ubah']" />
    </x-slot>


    <x-card>
        @include('drivers.partials.form', ['action' => route('drivers.update', $driver), 'method' => 'PUT', 'driver' => $driver])
    </x-card>
</x-app-layout>
