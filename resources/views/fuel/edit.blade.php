<x-app-layout title="Ubah Transaksi BBM">
    <x-slot name="header">
        <x-page-header title="Ubah Transaksi BBM" :subtitle="'Nota '.$transaction->receipt_number" :crumbs="['Operasional', 'BBM', 'Ubah']" />
    </x-slot>


    <x-card>
        @include('fuel.partials.form', [
            'action' => route('fuel.update', $transaction),
            'method' => 'PUT',
            'transaction' => $transaction,
        ])
    </x-card>
</x-app-layout>
