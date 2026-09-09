<x-app-layout title="Tambah Pengguna">
    <x-slot name="header">
        <x-page-header title="Tambah Pengguna" subtitle="Akun pengguna beserta penetapan peran" :crumbs="['Administrasi', 'Pengguna', 'Tambah']" />
    </x-slot>


    <x-card>
        @include('users.partials.form', ['action' => route('users.store'), 'method' => 'POST', 'user' => null])
    </x-card>
</x-app-layout>
