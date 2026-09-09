<x-app-layout title="Dashboard">
    <x-slot name="header">
        <x-page-header title="Dashboard" subtitle="Ringkasan Operasional by System — {{ now()->translatedFormat('l, d F Y') }}" />
    </x-slot>


    @if ($personal)
        {{-- FR-M6-06 — Karyawan & Driver hanya melihat aktivitas dirinya sendiri. --}}
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-stat-card label="Peminjaman Aktif" :value="$stats['peminjaman_aktif']" icon="calendar" accent="brand" />
                <x-stat-card label="Menunggu Approval" :value="$stats['menunggu_approval']" icon="exclamation" accent="warning" />
                <x-stat-card label="Klaim Diajukan" :value="$stats['klaim_diajukan']" icon="droplet" accent="brand" />
                <x-stat-card
                    label="Klaim Belum Dibayar"
                    value="Rp {{ number_format($stats['klaim_outstanding'], 0, ',', '.') }}"
                    icon="chart-bar"
                    accent="violet"
                />
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <x-card title="Peminjaman Saya" subtitle="Lima pengajuan terakhir" :padded="false">
                    <ul class="divide-y divide-gray-100">
                        @forelse ($myBookings as $b)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('bookings.show', $b) }}" class="truncate text-sm font-medium text-gray-900 hover:text-usc-700">
                                        {{ $b->booking_number }}
                                    </a>
                                    <p class="truncate text-xs text-gray-500">
                                        {{ $b->destination }} &middot; {{ $b->booking_date->format('d-m-Y') }}
                                        @if ($b->vehicle) &middot; {{ $b->vehicle->plate_number }} @endif
                                    </p>
                                </div>
                                <x-status-badge :status="$b->status" />
                            </li>
                        @empty
                            <li class="px-5 py-6 text-center text-sm text-gray-400">Belum ada pengajuan.</li>
                        @endforelse
                    </ul>
                    <div class="border-t border-gray-100 px-5 py-2.5 text-center">
                        <a href="{{ route('bookings.index') }}" class="text-xs font-medium text-usc-600 hover:text-usc-700">Lihat semua &rarr;</a>
                    </div>
                </x-card>

                <x-card title="Klaim BBM Saya" subtitle="Lima klaim terakhir" :padded="false">
                    <ul class="divide-y divide-gray-100">
                        @forelse ($myClaims as $c)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('fuel.show', $c) }}" class="truncate text-sm font-medium text-gray-900 hover:text-usc-700">
                                        {{ $c->vehicle?->plate_number }} &middot; Rp {{ number_format($c->claimable_amount, 0, ',', '.') }}
                                    </a>
                                    <p class="truncate text-xs text-gray-500">
                                        {{ $c->station_name }} &middot; {{ $c->transaction_datetime->format('d-m-Y') }}
                                    </p>
                                </div>
                                <x-status-badge :status="$c->status" />
                            </li>
                        @empty
                            <li class="px-5 py-6 text-center text-sm text-gray-400">Belum ada klaim BBM.</li>
                        @endforelse
                    </ul>
                    <div class="border-t border-gray-100 px-5 py-2.5 text-center">
                        <a href="{{ route('fuel.my-claims') }}" class="text-xs font-medium text-usc-600 hover:text-usc-700">Lihat semua klaim &rarr;</a>
                    </div>
                </x-card>
            </div>
        </div>
    @else
        <div class="space-y-6">
            {{-- FR-M6-01 — kartu ringkasan armada. --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <x-stat-card label="Total Kendaraan" :value="$stats['total_kendaraan']" icon="logo" accent="brand" />
                <x-stat-card label="Tersedia" :value="$stats['tersedia']" icon="check-circle" accent="good" />
                <x-stat-card label="Sedang Dipinjam" :value="$stats['dipinjam']" icon="calendar" accent="brand" />
                <x-stat-card label="Dalam Servis" :value="$stats['servis']" icon="wrench" accent="warning" />
                <x-stat-card label="Menunggu Approval" :value="$stats['menunggu_approval']" icon="exclamation" accent="warning" />
                <x-stat-card label="Servis Jatuh Tempo" :value="$stats['servis_jatuh_tempo']" icon="wrench" accent="critical" />
                @if ($pendingClaims > 0)
                    <x-stat-card label="Klaim BBM Menunggu" :value="$pendingClaims" icon="droplet" accent="warning" />
                @endif
                <x-stat-card
                    label="Biaya Bulan Ini"
                    value="Rp {{ number_format($stats['biaya_bulan_ini'], 0, ',', '.') }}"
                    icon="chart-bar"
                    accent="violet"
                />
            </div>

            {{-- FR-M6-02 — grafik tren biaya & kendaraan paling boros. --}}
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <x-card title="Tren Biaya Operasional 12 Bulan Terakhir" subtitle="BBM, Tol, dan Servis" class="xl:col-span-2">
                    <div class="h-72">
                        <canvas id="chart-trend"></canvas>
                    </div>
                </x-card>

                <x-card title="Top 5 Kendaraan Paling Boros" subtitle="Total biaya operasional bulan berjalan">
                    <div class="h-72">
                        <canvas id="chart-costly"></canvas>
                    </div>
                </x-card>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <x-card title="Utilisasi Kendaraan" subtitle="Persentasi (%) hari terpakai pada bulan berjalan" :padded="false">
                    <div class="space-y-4 px-5 py-4">
                        @forelse ($utilization as $u)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ $u['vehicle'] }}</span>
                                    <span class="text-gray-500">{{ $u['utilization_percent'] }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-usc-100">
                                    <div class="h-2 rounded-full bg-usc-500" style="width: {{ min(100, $u['utilization_percent']) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-gray-400">Belum ada data utilisasi.</p>
                        @endforelse
                    </div>
                    <div class="border-t border-gray-100 px-5 py-2.5 text-center">
                        <a href="{{ route('reports.index') }}" class="text-xs font-medium text-usc-600 hover:text-usc-700">Lihat laporan lengkap &rarr;</a>
                    </div>
                </x-card>

                {{-- FR-M4-06 — servis mendesak, terurut dari yang paling kritis. --}}
                <x-card title="Servis Segera & Jatuh Tempo" subtitle="Terurut dari yang paling mendesak" :padded="false">
                    <ul class="divide-y divide-gray-100">
                        @forelse ($urgentServices as $s)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900">
                                        {{ $s->vehicle?->plate_number }} &middot; {{ $s->serviceType?->name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ (int) $s->remaining_km < 0
                                            ? 'Terlewat '.number_format(abs((int) $s->remaining_km), 0, ',', '.').' km'
                                            : 'Sisa '.number_format((int) $s->remaining_km, 0, ',', '.').' km' }}
                                    </p>
                                </div>
                                <x-status-badge :status="$s->status" />
                            </li>
                        @empty
                            <li class="px-5 py-6 text-center text-sm text-gray-400">Tidak ada servis mendesak.</li>
                        @endforelse
                    </ul>
                    <div class="border-t border-gray-100 px-5 py-2.5 text-center">
                        <a href="{{ route('service.index') }}" class="text-xs font-medium text-usc-600 hover:text-usc-700">Lihat semua jadwal servis &rarr;</a>
                    </div>
                </x-card>

                {{-- FR-M2-16 — antrean approval Admin GA. --}}
                <x-card title="Peminjaman Menunggu Approval" subtitle="Antrean Admin GA" :padded="false">
                    <ul class="divide-y divide-gray-100">
                        @forelse ($pendingBookings as $b)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('bookings.show', $b) }}" class="truncate text-sm font-medium text-gray-900 hover:text-usc-700">
                                        {{ $b->requester?->name }}
                                    </a>
                                    <p class="truncate text-xs text-gray-500">
                                        {{ $b->destination }} &middot; {{ $b->booking_date->format('d-m-Y') }}
                                    </p>
                                </div>
                                @if ($b->is_urgent)
                                    <span class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-medium text-red-600 ring-1 ring-inset ring-red-600/20">Urgent</span>
                                @endif
                            </li>
                        @empty
                            <li class="px-5 py-6 text-center text-sm text-gray-400">Tidak ada pengajuan menunggu.</li>
                        @endforelse
                    </ul>
                    <div class="border-t border-gray-100 px-5 py-2.5 text-center">
                        <a href="{{ route('bookings.approval-queue') }}" class="text-xs font-medium text-usc-600 hover:text-usc-700">Buka antrean approval &rarr;</a>
                    </div>
                </x-card>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const trend = @json($trend);
                    const topCostly = @json($topCostly);

                    const palette = {
                        bbm: '#1DA338',
                        tol: '#B9E2C1',
                        servis: '#0C4418',
                        single: '#B9E2C1',
                        grid: '#eef0f2',
                    };

                    // Nilai dari server dalam rupiah penuh; sumbu ditampilkan dalam juta.
                    const toJuta = (v) => v / 1000000;
                    const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');

                    new Chart(document.getElementById('chart-trend'), {
                        type: 'bar',
                        data: {
                            labels: trend.months,
                            datasets: [
                                { label: 'BBM', data: trend.bbm.map(toJuta), backgroundColor: palette.bbm, borderRadius: 4, maxBarThickness: 22 },
                                { label: 'Tol', data: trend.tol.map(toJuta), backgroundColor: palette.tol, borderRadius: 4, maxBarThickness: 22 },
                                { label: 'Servis', data: trend.servis.map(toJuta), backgroundColor: palette.servis, borderRadius: 4, maxBarThickness: 22 },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'circle' } },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => `${ctx.dataset.label}: Rp ${ctx.parsed.y.toLocaleString('id-ID', { maximumFractionDigits: 2 })} jt`,
                                    },
                                },
                            },
                            scales: {
                                x: { stacked: true, grid: { display: false } },
                                y: {
                                    stacked: true,
                                    grid: { color: palette.grid },
                                    ticks: { callback: (v) => 'Rp ' + v + 'jt' },
                                },
                            },
                        },
                    });

                    new Chart(document.getElementById('chart-costly'), {
                        type: 'bar',
                        data: {
                            labels: topCostly.map((v) => v.plate_number),
                            datasets: [{
                                label: 'Total biaya',
                                data: topCostly.map((v) => v.total_cost),
                                backgroundColor: palette.single,
                                borderRadius: 4,
                                maxBarThickness: 18,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { label: (ctx) => rupiah(ctx.parsed.x) } },
                            },
                            scales: {
                                x: {
                                    grid: { color: palette.grid },
                                    ticks: { callback: (v) => 'Rp ' + toJuta(v).toLocaleString('id-ID') + 'jt' },
                                },
                                y: { grid: { display: false } },
                            },
                        },
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>

