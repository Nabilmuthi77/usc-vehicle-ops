<x-app-layout title="Log Notifikasi">
    <x-slot name="header">
        <x-page-header title="Riwayat Pengiriman Notifikasi" subtitle="Status berhasil/gagal kirim in-app dan email" :crumbs="['Notifikasi', 'Log']" />
    </x-slot>


    {{-- FR-M7-05 — log pengiriman untuk menelusuri email yang tidak sampai. --}}
    <x-card :padded="false">
        <form method="GET" action="{{ route('notifications.logs') }}">
            <x-filter-bar searchPlaceholder="Gunakan filter di samping..." name="q" :value="''">
                <x-filter-select name="status" :options="['' => 'Semua Status', 'terkirim' => 'Terkirim', 'gagal' => 'Gagal']"
                                 :selected="$filters['status'] ?? ''" />
                <x-filter-select name="event_key" :options="['' => 'Semua Kejadian'] + $events"
                                 :selected="$filters['event_key'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Penerima</th>
                        <th class="px-5 py-3">Kejadian</th>
                        <th class="px-5 py-3">Kanal</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $log->sent_at->format('d-m-Y H:i') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-900">
                                {{ $log->user?->name ?? '—' }}
                                <p class="text-xs text-gray-400">{{ $log->recipient }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ $events[$log->event_key] ?? $log->event_key }}
                                <p class="text-xs text-gray-400">{{ $log->subject }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                {{ $log->channel === 'mail' ? 'Email' : 'In-App' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $log->status === 'gagal' ? 'bg-red-50 text-red-700 ring-red-600/20' : 'bg-green-50 text-green-700 ring-green-600/20' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                                @if ($log->error_message)
                                    <p class="mt-1 max-w-[220px] truncate text-[11px] text-gray-400" title="{{ $log->error_message }}">
                                        {{ $log->error_message }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="5" message="Belum ada riwayat pengiriman notifikasi." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$logs" />
    </x-card>
</x-app-layout>

