@php
    $roleColors = [
        'Admin Sistem' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        'Admin GA' => 'bg-usc-50 text-usc-700 ring-usc-600/20',
        'Karyawan' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
        'Driver' => 'bg-green-50 text-green-700 ring-green-600/20',
        'Viewer' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
    ];
@endphp

<x-app-layout title="Pengguna">
    <x-slot name="header">
        <x-page-header title="Manajemen Pengguna" subtitle="Akun pengguna dan penetapan peran (RBAC)" :crumbs="['Administrasi', 'Pengguna']">
            <x-slot name="actions">
                <a href="{{ route('audit-log') }}"
                   class="inline-flex w-[45%] sm:w-auto justify-center items-center gap-1.5 rounded-lg bg-white px-2 sm:px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-usc-700 hover:bg-usc-50">
                    <x-nav-icon name="chart-bar" class="h-4 w-4 shrink-0" />
                    <span class="truncate">Audit Log</span>
                </a>
                <x-btn-primary href="{{ route('users.create') }}" icon="plus" class="w-[55%] sm:w-auto justify-center px-2 sm:px-3.5 text-sm">
                    <span class="truncate">Tambah Pengguna</span>
                </x-btn-primary>
            </x-slot>
        </x-page-header>
    </x-slot>


    {{-- PRD §5.1 — ringkasan peran beserta jumlah penggunanya. --}}
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach ($roles as $role)
            <x-card>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">
                    {{ $role->name }}
                </span>
                <p class="mt-2 text-lg font-semibold text-gray-900">{{ $role->users_count }} pengguna</p>
            </x-card>
        @endforeach
    </div>

    <x-card :padded="false">
        <form method="GET" action="{{ route('users.index') }}">
            <x-filter-bar searchPlaceholder="Cari nama, email, atau username..." :value="$filters['search'] ?? ''">
                <x-filter-select name="role" :options="['' => 'Semua Peran'] + $roles->pluck('name', 'name')->all()"
                                 :selected="$filters['role'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Departemen</th>
                        <th class="px-5 py-3">Peran</th>
                        <th class="px-5 py-3">Login Terakhir</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">
                                {{ $u->name }}
                                @if ($u->username)
                                    <p class="text-xs font-normal text-gray-400">{{ '@'.$u->username }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $u->email }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $u->department?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @foreach ($u->roles as $role)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $u->last_login_at?->format('d-m-Y H:i') ?? 'Belum pernah' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-status-badge :status="$u->is_active ? 'aktif' : 'nonaktif'" />
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('users.edit', $u) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    Ubah
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada pengguna." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$users" />
    </x-card>
</x-app-layout>

