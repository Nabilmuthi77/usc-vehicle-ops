<x-app-layout title="Ubah Kendaraan">
    <x-slot name="header">
        <x-page-header :title="'Ubah '.$vehicle->plate_number" :subtitle="$vehicle->full_name" :crumbs="['Master Data', 'Kendaraan', 'Ubah']" />
    </x-slot>


    <x-card class="max-w-4xl">
        @include('vehicles.partials.form', ['action' => route('vehicles.update', $vehicle), 'method' => 'PUT', 'vehicle' => $vehicle])
    </x-card>
</x-app-layout>
