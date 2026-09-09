<x-app-layout title="Departemen">
    <x-slot name="header">
        <x-page-header title="Master Data Departemen" subtitle="Cost center untuk alokasi biaya operasional" :crumbs="['Master Data', 'Departemen']">
            <x-slot name="actions">
                @can('master-data.kelola')
                    <a href="{{ route('departments.create') }}">
                        <x-btn-primary icon="plus">Tambah Departemen</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <x-card :padded="false">
        <form method="GET" action="{{ route('departments.index') }}">
            <x-filter-bar searchPlaceholder="Cari nama atau kode departemen..." :value="$filters['search'] ?? ''">
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama Departemen</th>
                        <th class="px-5 py-3">PIC</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Pengguna</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($departments as $d)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                    {{ $d->code }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">{{ $d->name }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $d->pic_name ?? '-' }}
                                @if ($d->pic_phone)
                                    <p class="text-xs text-gray-400">{{ $d->pic_phone }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 tabular-nums text-gray-600">{{ $d->vehicles_count }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 tabular-nums text-gray-600">{{ $d->users_count }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-status-badge :status="$d->is_active ? 'aktif' : 'nonaktif'" />
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                @can('master-data.kelola')
                                    <a href="{{ route('departments.edit', $d) }}"
                                       class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                        Ubah
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada departemen." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$departments" />
    </x-card>
</x-app-layout>

