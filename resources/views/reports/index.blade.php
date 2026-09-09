<x-app-layout title="Laporan & Export">
    <x-slot name="header">
        <x-page-header title="Laporan & Export" subtitle="Rekap biaya, aktivitas dan utilisasi" :crumbs="['Laporan & Export']">
        </x-page-header>
    </x-slot>

    {{-- WRAPPER 1: Biaya Operasional --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <h2 class="text-xl font-bold text-gray-900">Biaya Operasional</h2>
            @can('laporan.export')
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.export.excel', array_merge($costFilters, ['type' => 'cost'])) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-700 px-3 py-1.5 text-sm font-medium text-white shadow-md shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-800 hover:shadow-lg hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        <x-nav-icon name="document-text" class="h-4 w-4" /> Excel
                    </a>
                    <a href="{{ route('reports.export.pdf', array_merge($costFilters, ['type' => 'cost'])) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-rose-600 to-red-700 px-3 py-1.5 text-sm font-medium text-white shadow-md shadow-rose-600/30 hover:from-rose-700 hover:to-red-800 hover:shadow-lg hover:shadow-rose-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        <x-nav-icon name="document" class="h-4 w-4" /> PDF
                    </a>
                </div>
            @endcan
        </div>

        <x-card>
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="activity_from" value="{{ $activityFilters['from'] }}">
                <input type="hidden" name="activity_to" value="{{ $activityFilters['to'] }}">
                
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Dari Tanggal</label>
                    <x-date-picker name="cost_from" :value="$costFilters['from']" />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Sampai Tanggal</label>
                    <x-date-picker name="cost_to" :value="$costFilters['to']" />
                </div>

                
                <div class="w-full sm:w-auto flex items-center gap-2 mt-2 sm:mt-0">
                    <button type="submit" class="flex-1 sm:flex-none flex items-center justify-center rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-5 py-1 text-sm font-medium text-white shadow-md shadow-usc-500/20 hover:from-usc-600 hover:to-emerald-600 hover:shadow-lg hover:shadow-usc-500/25 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 h-[34px]">
                        Terapkan
                    </button>
                    <a href="{{ route('reports.index') }}" title="Reset Filter" class="shrink-0 group inline-flex items-center justify-center rounded-xl bg-white w-[34px] h-[34px] border border-slate-200 hover:border-slate-300 hover:bg-gray-50 hover:text-black hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        <x-nav-icon name="arrow-path" class="h-4 w-4 text-slate-500 group-hover:text-black transition-colors" />
                    </a>
                </div>
            </form>
        </x-card>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-3 xl:grid-cols-5">
            <x-stat-card label="Total Biaya BBM" value="Rp {{ number_format($totals['bbm'], 0, ',', '.') }}" icon="droplet" accent="brand" />
            <x-stat-card label="Total Biaya Tol" value="Rp {{ number_format($totals['tol'], 0, ',', '.') }}" icon="ticket" accent="brand" />
            <x-stat-card label="Total Biaya Servis" value="Rp {{ number_format($totals['servis'], 0, ',', '.') }}" icon="wrench" accent="warning" />
            <x-stat-card label="Total Biaya Operasional" value="Rp {{ number_format($totals['total'], 0, ',', '.') }}" icon="chart-bar" accent="good" />
            <x-stat-card label="Total Jarak Tempuh" value="{{ number_format($totals['km'], 0, ',', '.') }} km" icon="map" accent="brand" />
        </div>

        <x-card :padded="false">
            <div class="border-b border-gray-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-gray-900">Biaya Operasional per Kendaraan</h3>
                <p class="text-xs text-gray-500">Biaya servis kendaraan sewa yang ditanggung vendor dikecualikan dari total (BR-09).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-white/80 backdrop-blur">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="px-5 py-3">Kendaraan</th>
                            <th class="px-5 py-3">Departemen</th>
                            <th class="px-5 py-3 text-right">BBM</th>
                            <th class="px-5 py-3 text-right">Tol</th>
                            <th class="px-5 py-3 text-right">Servis</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Jarak</th>
                            <th class="px-5 py-3 text-right">Biaya/km</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($costByVehicle as $row)
                            <tr class="hover:bg-gray-50/60">
                                <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">{{ $row['plate_number'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $row['department'] ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums text-gray-600">{{ number_format($row['fuel_cost'], 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums text-gray-600">{{ number_format($row['toll_cost'], 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums text-gray-600">
                                    {{ number_format($row['service_cost'], 0, ',', '.') }}
                                    @if ($row['vendor_borne_cost'] > 0)
                                        <p class="text-[11px] text-gray-400">vendor: {{ number_format($row['vendor_borne_cost'], 0, ',', '.') }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums font-medium text-gray-900">{{ number_format($row['total_cost'], 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums text-gray-600">{{ number_format($row['distance_km'], 0, ',', '.') }} km</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums text-gray-600">{{ number_format($row['cost_per_km'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <x-empty-row :colspan="8" message="Tidak ada data pada periode ini." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    {{-- WRAPPER 2: Utilisasi & Aktivitas --}}
    <div class="mt-12 space-y-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <h2 class="text-xl font-bold text-gray-900">Utilisasi Kendaraan & Aktivitas Driver</h2>

        </div>

        <x-card>
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="cost_from" value="{{ $costFilters['from'] }}">
                <input type="hidden" name="cost_to" value="{{ $costFilters['to'] }}">

                
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Dari Tanggal</label>
                    <x-date-picker name="activity_from" :value="$activityFilters['from']" />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Sampai Tanggal</label>
                    <x-date-picker name="activity_to" :value="$activityFilters['to']" />
                </div>
                
                <div class="w-full sm:w-auto flex items-center gap-2 mt-2 sm:mt-0">
                    <button type="submit" class="flex-1 sm:flex-none flex items-center justify-center rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-5 py-1 text-sm font-medium text-white shadow-md shadow-usc-500/20 hover:from-usc-600 hover:to-emerald-600 hover:shadow-lg hover:shadow-usc-500/25 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 h-[34px]">
                        Terapkan
                    </button>
                    <a href="{{ route('reports.index') }}" title="Reset Filter" class="shrink-0 group inline-flex items-center justify-center rounded-xl bg-white w-[34px] h-[34px] border border-slate-200 hover:border-slate-300 hover:bg-gray-50 hover:text-black hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        <x-nav-icon name="arrow-path" class="h-4 w-4 text-slate-500 group-hover:text-black transition-colors" />
                    </a>
                </div>
            </form>
        </x-card>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-card :padded="false">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">Utilisasi Kendaraan</h3>
                    @can('laporan.export')
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('reports.export.excel', array_merge($activityFilters, ['type' => 'utilization'])) }}" class="inline-flex items-center gap-1 rounded-md bg-gradient-to-r from-emerald-600 to-teal-700 px-2.5 py-1 text-xs font-medium text-white shadow-sm shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-800 hover:shadow hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"><x-nav-icon name="document-text" class="h-3.5 w-3.5" /> Excel</a>
                            <a href="{{ route('reports.export.pdf', array_merge($activityFilters, ['type' => 'utilization'])) }}" class="inline-flex items-center gap-1 rounded-md bg-gradient-to-r from-rose-600 to-red-700 px-2.5 py-1 text-xs font-medium text-white shadow-sm shadow-rose-600/30 hover:from-rose-700 hover:to-red-800 hover:shadow hover:shadow-rose-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"><x-nav-icon name="document" class="h-3.5 w-3.5" /> PDF</a>
                        </div>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-white/80 backdrop-blur">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-3">Kendaraan</th>
                                <th class="px-5 py-3 text-right">Hari Terpakai</th>
                                <th class="px-5 py-3 text-right">Perjalanan</th>
                                <th class="px-5 py-3 text-right">Utilisasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($utilization as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $row['vehicle'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['days_used'] }} / {{ $row['total_days'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['trips'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums font-medium text-gray-900">{{ $row['utilization_percent'] }}%</td>
                                </tr>
                            @empty
                                <x-empty-row :colspan="4" message="Tidak ada data utilisasi." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card :padded="false">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">Aktivitas Driver</h3>
                    @can('laporan.export')
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('reports.export.excel', array_merge($activityFilters, ['type' => 'driver'])) }}" class="inline-flex items-center gap-1 rounded-md bg-gradient-to-r from-emerald-600 to-teal-700 px-2.5 py-1 text-xs font-medium text-white shadow-sm shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-800 hover:shadow hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"><x-nav-icon name="document-text" class="h-3.5 w-3.5" /> Excel</a>
                            <a href="{{ route('reports.export.pdf', array_merge($activityFilters, ['type' => 'driver'])) }}" class="inline-flex items-center gap-1 rounded-md bg-gradient-to-r from-rose-600 to-red-700 px-2.5 py-1 text-xs font-medium text-white shadow-sm shadow-rose-600/30 hover:from-rose-700 hover:to-red-800 hover:shadow hover:shadow-rose-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"><x-nav-icon name="document" class="h-3.5 w-3.5" /> PDF</a>
                        </div>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-white/80 backdrop-blur">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-3">Driver</th>
                                <th class="px-5 py-3 text-right">Perjalanan</th>
                                <th class="px-5 py-3 text-right">Jarak</th>
                                <th class="px-5 py-3 text-right">Konsumsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($driverActivity as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $row['driver'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['trips'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ number_format($row['distance_km'], 0, ',', '.') }} km</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">
                                        {{ $row['avg_consumption'] ? number_format($row['avg_consumption'], 2, ',', '.').' km/L' : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <x-empty-row :colspan="4" message="Tidak ada aktivitas driver." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    {{-- WRAPPER 3: Outstanding Reimbursement --}}
    <div class="mt-12 space-y-6">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <h2 class="text-xl font-bold text-gray-900">Outstanding Reimbursement</h2>
            @can('laporan.export')
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.export.excel', ['type' => 'outstanding']) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-700 px-3 py-1.5 text-sm font-medium text-white shadow-md shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-800 hover:shadow-lg hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        <x-nav-icon name="document-text" class="h-4 w-4" /> Excel
                    </a>
                    <a href="{{ route('reports.export.pdf', ['type' => 'outstanding']) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-rose-600 to-red-700 px-3 py-1.5 text-sm font-medium text-white shadow-md shadow-rose-600/30 hover:from-rose-700 hover:to-red-800 hover:shadow-lg hover:shadow-rose-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        <x-nav-icon name="document" class="h-4 w-4" /> PDF
                    </a>
                </div>
            @endcan
        </div>

        <x-card :padded="false">
            <div class="border-b border-gray-100 px-5 py-3">
                <p class="text-xs text-gray-500">Klaim terverifikasi yang belum dibayar — kewajiban perusahaan kepada driver (BR-14).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-white/80 backdrop-blur">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="px-5 py-3">Pengaju</th>
                            <th class="px-5 py-3 text-right">Jumlah Klaim</th>
                            <th class="px-5 py-3 text-right">Total Nominal</th>
                            <th class="px-5 py-3 text-right">Umur Klaim</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($outstanding as $row)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $row['claimant'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['item_count'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums font-medium text-gray-900">
                                    Rp {{ number_format($row['total_amount'], 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['age_days'] }} hari</td>
                            </tr>
                        @empty
                            <x-empty-row :colspan="4" message="Tidak ada klaim outstanding." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>

