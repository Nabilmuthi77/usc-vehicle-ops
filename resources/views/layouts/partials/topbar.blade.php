@php
    $topbarNotifications = auth()->user()->notifications()->latest()->limit(5)->get();
    $unreadCount         = auth()->user()->unreadNotifications()->count();
@endphp

<div class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
    {{-- Mobile hamburger --}}
    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden">
        <span class="sr-only">Buka menu</span>
        <x-nav-icon name="menu" class="h-6 w-6" />
    </button>

    {{-- Search --}}
    <div class="flex-1 max-w-md min-w-0" x-data="{
        q: '',
        results: [],
        loading: false,
        open: false,
        debounce: null,
        search() {
            clearTimeout(this.debounce);
            if (this.q.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }
            this.debounce = setTimeout(() => {
                this.loading = true;
                this.open = true;
                fetch('{{ route('search') }}?q=' + encodeURIComponent(this.q), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    this.results = data.results || [];
                    this.loading = false;
                });
            }, 300);
        }
    }" @click.outside="open = false">
        <div class="relative">
            <x-nav-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input
                type="text"
                x-model="q"
                @input="search()"
                @focus="q.length >= 2 ? open = true : null"
                @if(auth()->user()->hasAnyRole(['Admin Sistem', 'Admin GA']))
                    placeholder="Cari kendaraan, pengajuan, nota, servis..."
                @else
                    placeholder="Cari pengajuan, nomor nota BBM..."
                @endif
                class="block w-full h-[34px] rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
            >
            <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2" style="display: none;">
                <svg class="h-4 w-4 animate-spin text-usc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
            
            <div x-show="open" x-transition class="absolute left-0 mt-2 w-full origin-top-left rounded-xl bg-white shadow-lg ring-1 ring-gray-900/5 z-50 overflow-hidden" style="display: none;">
                <template x-if="results.length > 0 && !loading">
                    <ul class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                        <template x-for="res in results">
                            <li>
                                <a :href="res.url" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-usc-50 text-usc-600">
                                        <svg x-show="res.icon === 'calendar'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <svg x-show="res.icon === 'truck'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 19h8M5 19h1a2 2 0 002-2v-3a2 2 0 00-2-2H5m0 7a2 2 0 01-2-2v-5a2 2 0 012-2m0 7v-7m11 0h3a2 2 0 012 2v3a2 2 0 01-2 2h-1m-4 0a2 2 0 01-2-2v-5a2 2 0 012-2m0 7v-7m4 7h1M13 9h3M9 9h3"/></svg>
                                        <svg x-show="res.icon === 'droplet'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        <svg x-show="res.icon === 'wrench'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900" x-text="res.title"></p>
                                        <p class="truncate text-xs text-gray-500"><span x-text="res.type" class="font-semibold text-usc-600"></span> - <span x-text="res.subtitle"></span></p>
                                    </div>
                                </a>
                            </li>
                        </template>
                    </ul>
                </template>
                <template x-if="results.length === 0 && !loading">
                    <div class="px-4 py-8 text-center text-sm text-gray-500">
                        Tidak ada hasil yang ditemukan untuk pencarian &quot;<span x-text="q" class="font-semibold"></span>&quot;.
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 shrink-0 ml-auto">
        {{-- Notifications --}}
        <div class="relative" x-data="{
            open: false,
            listen() {
                if (window.Echo) {
                    window.Echo.private('App.Models.User.{{ auth()->id() }}')
                        .notification((notification) => {
                            if (!this.open) {
                                fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(res => res.text())
                                    .then(html => {
                                        const doc = new DOMParser().parseFromString(html, 'text/html');
                                        const wrapper = doc.querySelector('#topbar-notifications-wrapper');
                                        if (wrapper && this.$refs.wrapper) {
                                            this.$refs.wrapper.innerHTML = wrapper.innerHTML;
                                        }
                                    });
                            }
                        });
                }
            }
        }" x-init="listen()" @click.outside="open = false">
            <div id="topbar-notifications-wrapper" x-ref="wrapper">
                <button @click="open = !open" class="relative rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                    <span class="sr-only">Notifikasi</span>
                    <x-nav-icon name="bell" class="h-5 w-5" />
                    @if ($unreadCount > 0)
                        <span class="absolute right-1.5 top-1.5 flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-usc-500 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-usc-500"></span>
                        </span>
                    @endif
                </button>

                <div
                    x-show="open"
                    x-transition
                    class="fixed left-1/2 top-16 z-30 mt-2 w-[calc(100vw-2rem)] -translate-x-1/2 sm:absolute sm:left-auto sm:top-auto sm:right-0 sm:mt-2 sm:w-96 sm:translate-x-0 sm:origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-gray-900/5"
                    style="display: none;"
                >
                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                        <p class="text-sm font-semibold text-gray-900">Notifikasi</p>
                        <span class="text-xs font-medium text-usc-600">{{ $unreadCount }} belum dibaca</span>
                    </div>
                    <ul class="max-h-80 divide-y divide-gray-100 overflow-y-auto">
                        @forelse ($topbarNotifications as $n)
                            @php $data = $n->data; $href = route('notifications.click', $n->id); @endphp
                            <a href="{{ $href }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 {{ $n->read_at === null ? 'bg-usc-50/40' : '' }}">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-usc-50 text-usc-600">
                                    <x-nav-icon :name="$data['icon'] ?? 'bell'" class="h-4 w-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ $data['title'] ?? 'Notifikasi' }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $data['detail'] ?? '' }}</p>
                                    <p class="mt-0.5 text-[11px] text-gray-400">{{ $n->created_at->diffForHumans() }}</p>
                                </div>
                                @if ($n->read_at === null)
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-usc-500"></span>
                                @endif
                            </a>
                        @empty
                            <li class="px-4 py-8 text-center text-sm text-gray-400">Belum ada notifikasi.</li>
                        @endforelse
                    </ul>
                    <div class="border-t border-gray-100 px-4 py-2.5 text-center">
                        <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-usc-600 hover:text-usc-700">Lihat semua notifikasi</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- User menu --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="flex items-center gap-2 rounded-full p-1 sm:py-1.5 sm:pl-1.5 sm:pr-3 hover:bg-gray-100 transition-colors">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-usc-50 ring-2 ring-usc-100">
                    @if(Auth::user()->photo_profile)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url(Auth::user()->photo_profile) }}" alt="Avatar" class="h-9 w-9 rounded-full object-cover">
                    @else
                        <span class="text-sm font-bold text-usc-600">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                    @endif
                </div>
                <span class="hidden text-left sm:block">
                    <span class="block text-sm font-medium leading-none text-gray-700">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4 text-gray-400 sm:block transition-transform duration-300 ease-out" x-bind:class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                class="absolute right-0 z-30 mt-2 sm:mt-1 w-56 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-gray-900/5 p-1"
                style="display: none;"
            >
                <div class="px-3 py-2.5 mb-1 border-b border-gray-100">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                    <p class="truncate text-xs text-gray-500">{{ Auth::user()->email ?? '' }}</p>
                </div>
                
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                    <x-nav-icon name="user" class="h-4 w-4 text-gray-400" />
                    Profil Saya
                </a>
                
                <div class="my-1 border-t border-gray-100"></div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 hover:text-red-700 transition-colors">
                        <x-nav-icon name="logout" class="h-4 w-4" />
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
