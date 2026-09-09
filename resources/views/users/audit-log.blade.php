<x-app-layout title="Audit Log">
    <x-slot name="header">
        <x-page-header title="Audit Log" subtitle="Seluruh perubahan data transaksional beserta pengguna dan waktunya" :crumbs="['Administrasi', 'Audit Log']" />
    </x-slot>


    {{-- FR-M1-10 — audit seluruh aksi create/update/delete. --}}
    <x-card :padded="false">
        <form method="GET" action="{{ route('audit-log') }}">
            <x-filter-bar searchPlaceholder="Cari waktu, pengguna, entitas, aksi..." name="search" :value="$filters['search'] ?? ''">
                <x-filter-select name="log_name" :options="['' => 'Semua Entitas'] + $logNames->mapWithKeys(fn ($n) => [$n => $n])->all()"
                                 :selected="$filters['log_name'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Pengguna</th>
                        <th class="px-5 py-3">Entitas</th>
                        <th class="px-5 py-3">Aksi</th>
                        <th class="px-5 py-3">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $activity->created_at->format('d-m-Y H:i') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-900">{{ $activity->causer?->name ?? 'Sistem' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                {{ $activity->log_name }}
                                <span class="text-xs text-gray-400">#{{ $activity->subject_id }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $activity->description }}</td>
                            <td class="px-5 py-3">
                                @php $attributes = $activity->properties['attributes'] ?? []; @endphp
                                @if (! empty($attributes))
                                    <details class="text-xs text-gray-600">
                                        <summary class="cursor-pointer text-usc-700">{{ count($attributes) }} kolom berubah</summary>
                                        <ul class="mt-1 space-y-0.5">
                                            @foreach ($attributes as $key => $newValue)
                                                <li>
                                                    <span class="font-medium">{{ $key }}:</span>
                                                    @php
                                                        $oldVal = $activity->properties['old'][$key] ?? '—';
                                                        $oldVal = is_array($oldVal) ? json_encode($oldVal) : (string) $oldVal;
                                                        $newVal = is_array($newValue) ? json_encode($newValue) : (string) $newValue;
                                                    @endphp
                                                    <span class="text-gray-400">{{ \Illuminate\Support\Str::limit($oldVal, 30) }}</span>
                                                    &rarr;
                                                    <span>{{ \Illuminate\Support\Str::limit($newVal, 30) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="5" message="Belum ada aktivitas tercatat." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$activities" />
    </x-card>
</x-app-layout>

