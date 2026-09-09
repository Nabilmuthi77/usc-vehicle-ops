<x-app-layout title="Input Pengisian BBM">
    <x-slot name="header">
        <x-page-header title="Input Pengisian BBM" subtitle="Nota wajib difoto untuk dapat diajukan sebagai klaim" :crumbs="['Operasional', 'BBM', 'Input']" />
    </x-slot>


    <x-card>
        @include('fuel.partials.form', [
            'action' => route('fuel.store'),
            'method' => 'POST',
            'transaction' => null,
        ])
    </x-card>
</x-app-layout>
