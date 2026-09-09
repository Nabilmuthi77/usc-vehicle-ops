<x-app-layout title="Ubah Pengguna">
    <x-slot name="header">
        <x-page-header :title="'Ubah '.$user->name" subtitle="Akun pengguna beserta penetapan peran" :crumbs="['Administrasi', 'Pengguna', 'Ubah']" />
    </x-slot>


    <x-card>
        @include('users.partials.form', ['action' => route('users.update', $user), 'method' => 'PUT', 'user' => $user])
    </x-card>
</x-app-layout>
