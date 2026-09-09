<x-app-layout title="Tambah Departemen">
    <x-slot name="header">
        <x-page-header title="Tambah Departemen" subtitle="Cost center untuk alokasi biaya operasional" :crumbs="['Master Data', 'Departemen', 'Tambah']" />
    </x-slot>


    <x-card>
        @include('departments.partials.form', ['action' => route('departments.store'), 'method' => 'POST', 'department' => null])
    </x-card>
</x-app-layout>
