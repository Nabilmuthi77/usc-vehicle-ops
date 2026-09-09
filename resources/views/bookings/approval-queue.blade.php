<x-app-layout title="Antrean Approval">
    <x-slot name="header">
        <x-page-header title="Antrean Approval Peminjaman"
                       subtitle="Seluruh pengajuan menunggu persetujuan, terurut dari tanggal pemakaian terdekat"
                       :crumbs="['Operasional', 'Peminjaman', 'Antrean Approval']" />
    </x-slot>


    <x-card :padded="false" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur border-b border-usc-100">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Pengajuan</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Tanggal Pemakaian</th>
                        <th class="px-5 py-3">Tujuan &amp; Keperluan</th>
                        <th class="px-5 py-3">Durasi</th>
                        <th class="px-5 py-3">Ganjil-Genap</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bookings as $b)
                        <tr class="hover:bg-usc-50/50 {{ $b->isImminent() ? 'bg-amber-50/40' : '' }}">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <span class="font-medium text-gray-900">{{ $b->booking_number }}</span>
                                @if ($b->is_urgent)
                                    <span class="ml-1.5 rounded-full bg-red-50 px-1.5 py-0.5 text-[10px] font-semibold text-red-600 ring-1 ring-inset ring-red-600/20">URGENT</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $b->requester?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $b->department?->name }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <span class="{{ $b->isImminent() ? 'font-semibold text-amber-700' : 'text-gray-900' }}">
                                    {{ $b->booking_date->format('d-m-Y') }}
                                </span>
                                @if ($b->isImminent())
                                    <p class="text-[11px] text-amber-600">
                                        {{ $b->booking_date->isToday() ? 'Hari ini' : 'Besok' }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                {{ $b->destination }}
                                <p class="max-w-[240px] truncate text-xs text-gray-500">{{ $b->purpose }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $b->duration_type->label() }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @if ($b->odd_even_zone)
                                    <span class="rounded-full bg-orange-50 px-2 py-0.5 text-[11px] font-medium text-orange-700">Ya</span>
                                @else
                                    <span class="text-xs text-gray-400">Tidak</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('bookings.show', $b) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    Proses
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Tidak ada pengajuan yang menunggu persetujuan." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>

