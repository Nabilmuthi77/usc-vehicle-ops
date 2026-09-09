<x-app-layout title="Papan Tugas Harian">
    <x-slot name="header">
        <x-page-header title="Papan Penugasan Harian"
                       :subtitle="'Seluruh penugasan pada '.$date->translatedFormat('l, d F Y')"
                       :crumbs="['Operasional', 'Peminjaman', 'Papan Tugas']">
            <x-slot name="actions">
                <form method="GET" action="{{ route('bookings.assignment-board') }}" class="flex items-center gap-2">
                    <div class="w-48">
                        <x-date-picker name="date" :value="$date->toDateString()" placeholder="Pilih tanggal" />
                    </div>
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-usc-500 to-emerald-500 px-3.5 py-2 text-sm font-medium text-white shadow-lg shadow-usc-500/20 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0 transition duration-300">
                        Tampilkan
                    </button>
                </form>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card :padded="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Driver</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Tujuan</th>
                        <th class="px-5 py-3">Durasi</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($assignments as $a)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-base font-semibold text-gray-900">{{ $a->vehicle?->plate_number }}</p>
                                <p class="text-xs text-gray-500">{{ $a->vehicle?->full_name }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $a->self_drive ? 'Self-drive' : $a->driver?->name }}</p>
                                @if (! $a->self_drive && $a->driver?->phone)
                                    <p class="text-xs text-gray-500">{{ $a->driver->phone }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="text-gray-900">{{ $a->requester?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $a->department?->name }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $a->destination }}
                                <p class="text-xs text-gray-500">{{ $a->purpose }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $a->duration_type->label() }}</td>
                            <td class="whitespace-nowrap px-5 py-4"><x-status-badge :status="$a->status" /></td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="6" message="Tidak ada penugasan pada tanggal ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>

