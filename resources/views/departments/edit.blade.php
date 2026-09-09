<x-app-layout title="Ubah Departemen">
    <x-slot name="header">
        <x-page-header :title="'Ubah '.$department->name" subtitle="Cost center untuk alokasi biaya operasional" :crumbs="['Master Data', 'Departemen', 'Ubah']" />
    </x-slot>


    <x-card>
        @include('departments.partials.form', ['action' => route('departments.update', $department), 'method' => 'PUT', 'department' => $department])
    </x-card>
</x-app-layout>
