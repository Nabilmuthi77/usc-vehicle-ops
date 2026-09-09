@php
    $user = auth()->user();

    $pendingBookings = \App\Models\Booking::whereNotIn('status', [
        \App\Enums\BookingStatus::Selesai,
        \App\Enums\BookingStatus::Dibatalkan,
        \App\Enums\BookingStatus::Ditolak,
    ])->when($user, function($q) use ($user) {
        if ($user && $user->seesOnlyOwnRecords()) {
            $q->ownedBy($user);
        }
    })->count();
    
    $dueServices = \App\Models\VehicleServiceSchedule::where('status', \App\Enums\ServiceScheduleStatus::JatuhTempo)->where('is_active', true)->count();
    $upcomingServices = \App\Models\VehicleServiceSchedule::where('status', \App\Enums\ServiceScheduleStatus::Segera)->where('is_active', true)->count();
    $activeServiceRequests = \App\Models\ServiceRequest::open()->count();

    $activeDriverAssignmentsQuery = \App\Models\Booking::whereNotNull('driver_id')
        ->whereIn('status', [\App\Enums\BookingStatus::Disetujui, \App\Enums\BookingStatus::SedangDigunakan]);
    
    if ($user->hasRole('Driver') && !$user->hasAnyRole(['Admin GA', 'Admin Sistem', 'Viewer'])) {
        $driverId = \App\Models\Driver::where('user_id', $user->id)->value('id');
        $activeDriverAssignmentsQuery->where('driver_id', $driverId ?: 0);
    }
    
    $activeDriverAssignments = $activeDriverAssignmentsQuery->count();

    $unpaidFuelClaimsAll = \App\Models\FuelTransaction::where('status', '!=', \App\Enums\FuelClaimStatus::Dibayar)->count();
    $unpaidFuelClaimsMe = \App\Models\FuelTransaction::where('status', '!=', \App\Enums\FuelClaimStatus::Dibayar)
        ->where('claimant_id', $user->id)->count();

    $outstandingReimbursementItems = \App\Models\FuelTransaction::outstanding()
        ->whereNull('reimbursement_batch_id')
        ->when(! $user->hasAnyRole(['Admin GA', 'Admin Sistem', 'Viewer']), fn($q) => $q->where('claimant_id', $user->id))
        ->count();
    
    $pendingReimbursementBatches = \App\Models\ReimbursementBatch::where('status', '!=', \App\Enums\ReimbursementBatchStatus::Dibayar)
        ->when(! $user->hasAnyRole(['Admin GA', 'Admin Sistem', 'Viewer']), fn($q) => $q->where('claimant_id', $user->id))
        ->count();

    $reimbursementBadge = $outstandingReimbursementItems + $pendingReimbursementBatches;

    $allNavGroups = [
        [
            'label' => 'Menu Utama',
            'items' => [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'label' => 'Operasional',
            'items' => [
                ['route' => 'bookings.index', 'label' => 'Peminjaman Kendaraan', 'icon' => 'calendar', 'badge' => $pendingBookings ?: null],
                ['route' => 'bookings.driver-assignments.index', 'label' => 'Penugasan Driver', 'icon' => 'user', 'badge' => $activeDriverAssignments ?: null, 'roles' => ['Driver', 'Admin GA', 'Admin Sistem', 'Viewer']],
                $user->can(\App\Support\Permissions::FUEL_VIEW_ALL) 
                    ? ['route' => 'fuel.index', 'label' => 'Pemakaian BBM', 'icon' => 'droplet', 'badge' => $unpaidFuelClaimsAll ?: null]
                    : ['route' => 'fuel.my-claims', 'label' => 'Klaim BBM', 'icon' => 'droplet', 'badge' => $unpaidFuelClaimsMe ?: null],
                ['route' => 'reimbursements.index', 'label' => 'Reimbursement <sup class="text-[9px]">BBM</sup>', 'icon' => 'credit-card', 'badge' => $reimbursementBadge ?: null],
                ['route' => 'service.index', 'label' => 'Servis Berkala', 'icon' => 'wrench', 'badge' => $dueServices ?: null, 'badgeColor' => 'critical', 'badge2' => $upcomingServices ?: null, 'roles' => ['Admin Sistem', 'Admin GA', 'Viewer']],
                ['route' => 'service-requests.index', 'label' => 'Pengajuan Servis', 'icon' => 'file-text', 'badge' => $activeServiceRequests ?: null, 'roles' => ['Admin Sistem', 'Admin GA', 'Viewer']],
                ['route' => 'toll.index', 'label' => 'Biaya Tol', 'icon' => 'ticket', 'roles' => ['Admin Sistem', 'Admin GA', 'Driver']],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                ['route' => 'reports.index', 'label' => 'Laporan & Export', 'icon' => 'chart-bar', 'roles' => ['Admin Sistem', 'Admin GA']],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
                ['route' => 'vehicles.index', 'label' => 'Kendaraan', 'icon' => 'truck', 'roles' => ['Admin Sistem', 'Admin GA']],
                ['route' => 'drivers.index', 'label' => 'Driver', 'icon' => 'user', 'roles' => ['Admin Sistem', 'Admin GA']],
                ['route' => 'vendors.index', 'label' => 'Vendor / Bengkel', 'icon' => 'briefcase', 'roles' => ['Admin Sistem', 'Admin GA']],
                ['route' => 'departments.index', 'label' => 'Departemen', 'icon' => 'users', 'roles' => ['Admin Sistem', 'Admin GA']],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['route' => 'users.index', 'label' => 'Pengguna', 'icon' => 'users', 'roles' => ['Admin Sistem']],
                ['route' => 'settings.index', 'label' => 'Pengaturan', 'icon' => 'settings', 'roles' => ['Admin Sistem']],
            ],
        ],
    ];

    // Filter menu berdasarkan role user
    $navGroups = [];
    foreach ($allNavGroups as $group) {
        $filteredItems = array_filter($group['items'], function($item) use ($user) {
            if (!isset($item['roles'])) {
                return true; // Bisa diakses semua
            }
            return $user->hasAnyRole($item['roles']);
        });

        if (!empty($filteredItems)) {
            $group['items'] = $filteredItems;
            $navGroups[] = $group;
        }
    }
@endphp

<div class="flex h-full flex-col bg-white border-r border-gray-200" x-data="{
    listenSidebar() {
        if (window.Echo) {
            window.Echo.private('App.Models.User.{{ auth()->id() }}')
                .notification((notification) => {
                    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => res.text())
                        .then(html => {
                            const doc = new DOMParser().parseFromString(html, 'text/html');
                            const wrapper = doc.querySelector('#sidebar-nav-wrapper');
                            if (wrapper && this.$refs.sidebarWrapper) {
                                this.$refs.sidebarWrapper.innerHTML = wrapper.innerHTML;
                            }
                        });
                });
        }
    }
}" x-init="listenSidebar()">
    <a href="{{ route('home') }}" class="flex h-16 shrink-0 items-center gap-3 px-5 border-b border-gray-200 hover:bg-gray-50 transition">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-usc-50 text-usc-500 border border-usc-100 shrink-0">
            <x-application-logo class="h-5 w-5" />
        </div>
        <div class="min-w-0">
            <p class="text-sm font-semibold leading-none text-gray-900">USC Vehicle Ops</p>
            <p class="mt-1 text-[11px] leading-none text-gray-500">Monitoring Kendaraan Ops.</p>
        </div>
    </a>

    <nav id="sidebar-nav-wrapper" x-ref="sidebarWrapper" class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        @foreach ($navGroups as $group)
            <div>
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                    {{ $group['label'] }}
                </p>
                <div class="mt-2 space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php
                            $active = request()->routeIs($item['route']);
                            $badgeColor = $item['badgeColor'] ?? 'brand';
                        @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                                {{ $active ? 'bg-usc-50 text-usc-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                        >
                            <x-nav-icon :name="$item['icon']" class="h-5 w-5 shrink-0 {{ $active ? 'text-usc-600' : 'text-gray-400 group-hover:text-gray-500' }}" />
                            <span class="truncate">{!! $item['label'] !!}</span>
                            @if (!empty($item['badge']) || !empty($item['badge2']))
                                <div class="ml-auto flex items-center gap-1.5">
                                    @if (!empty($item['badge2']))
                                        <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold bg-amber-100 text-amber-700">
                                            {{ $item['badge2'] }}
                                        </span>
                                    @endif
                                    @if (!empty($item['badge']))
                                        <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold
                                            {{ $badgeColor === 'critical' ? 'bg-red-100 text-red-700' : ($badgeColor === 'success' ? 'bg-green-50 text-green-700' : 'bg-usc-100 text-usc-700') }}">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-gray-200 p-4">
        <a href="{{ route('profile.edit') }}" class="group block rounded-xl p-3 transition hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-usc-50 ring-2 ring-usc-100">
                    @if(Auth::user()->photo_profile)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->photo_profile) }}" alt="Avatar" class="h-10 w-10 rounded-full object-cover">
                    @else
                        <span class="text-sm font-bold text-usc-600">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="truncate text-[11px] text-gray-500">{{ Auth::user()->roles->first()?->name ?? 'Staff' }}</p>
                </div>
                <div class="shrink-0">
                    <x-nav-icon name="more-vertical" class="h-5 w-5 text-gray-400 group-hover:text-gray-500" />
                </div>
            </div>
        </a>
    </div>
</div>


