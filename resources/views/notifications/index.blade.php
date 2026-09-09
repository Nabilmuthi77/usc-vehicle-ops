<x-app-layout title="Notifikasi">
    <x-slot name="header">
        <x-page-header title="Notifikasi" subtitle="Seluruh pemberitahuan in-app untuk akun Anda" :crumbs="['Notifikasi']">
            <x-slot name="actions">
                <a href="{{ route('notifications.preferences') }}"
                   class="inline-flex flex-[3] sm:flex-none justify-center items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-usc-700 hover:bg-usc-50 whitespace-nowrap">
                    <x-nav-icon name="wrench" class="h-4 w-4" />
                    Preferensi
                </a>
                <form method="POST" action="{{ route('notifications.read-all') }}" class="inline-flex flex-[7] sm:flex-none">
                    @csrf
                    <x-btn-primary type="submit" icon="check-circle" class="w-full justify-center whitespace-nowrap">Tandai Semua Dibaca</x-btn-primary>
                </form>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card :padded="false" x-data="{
        selected: [],
        editMode: false,
        listen() {
            if (window.Echo) {
                window.Echo.private('App.Models.User.{{ auth()->id() }}')
                    .notification((notification) => {
                        fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(res => res.text())
                            .then(html => {
                                const doc = new DOMParser().parseFromString(html, 'text/html');
                                const wrapper = doc.querySelector('#notifications-list-wrapper');
                                if (wrapper && this.$refs.wrapper && this.selected.length === 0 && !this.editMode) {
                                    this.$refs.wrapper.innerHTML = wrapper.innerHTML;
                                }
                            });
                    });
            }
        },
        toggleEditMode() {
            if (!this.editMode) {
                this.selected = [];
            }
        }
    }" x-init="listen()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-100 px-5 py-3 gap-3 sm:gap-0">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <input type="checkbox" x-model="editMode" @change="toggleEditMode()" title="Pilih notifikasi untuk dihapus" class="h-4 w-4 rounded border-gray-300 text-usc-600 focus:ring-usc-600">
                <p class="text-sm text-gray-600 shrink-0">
                    <span class="font-semibold text-gray-900">{{ $unreadCount }}</span> belum dibaca
                </p>
                <form method="POST" action="{{ route('notifications.bulk-destroy') }}" x-show="selected.length > 0" style="display: none;" class="flex items-center shrink-0">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="notifications[]" :value="id">
                    </template>
                    <button type="submit" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" onclick="return confirm('Hapus notifikasi terpilih?')">
                        Hapus (<span x-text="selected.length"></span>)
                    </button>
                </form>
            </div>
            <div class="flex items-center gap-2 text-sm shrink-0">
                <a href="{{ route('notifications.index') }}"
                   class="rounded-lg px-2.5 py-1.5 font-medium {{ empty($filters['unread']) ? 'bg-usc-50 text-usc-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Semua
                </a>
                <a href="{{ route('notifications.index', ['unread' => 1]) }}"
                   class="rounded-lg px-2.5 py-1.5 font-medium {{ ! empty($filters['unread']) ? 'bg-usc-50 text-usc-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Belum Dibaca
                </a>
            </div>
        </div>

        <div id="notifications-list-wrapper" x-ref="wrapper">

        <ul class="divide-y divide-gray-100">
            @forelse ($notifications as $notification)
                @php 
                    $data = $notification->data; 
                    $isUnread = $notification->read_at === null;
                    $href = route('notifications.click', $notification->id);
                    $canDelete = true;
                    $url = $data['action_url'] ?? '';
                    if (($data['category'] ?? '') === 'Peminjaman' && preg_match('/\/peminjaman\/(\d+)/', $url, $matches)) {
                        $booking = \App\Models\Booking::find($matches[1]);
                        if ($booking && in_array($booking->status, [
                            \App\Enums\BookingStatus::MenungguApproval,
                            \App\Enums\BookingStatus::Disetujui,
                            \App\Enums\BookingStatus::SedangDigunakan,
                        ])) {
                            $canDelete = false;
                        }
                    }
                @endphp
                <li class="relative flex gap-4 px-5 py-4 transition hover:bg-gray-50 {{ $isUnread ? 'bg-usc-50/40' : 'bg-white' }}"
                    @click="if (editMode && {{ $canDelete ? 'true' : 'false' }}) { const id = '{{ $notification->id }}'; if (selected.includes(id)) { selected = selected.filter(i => i !== id); } else { selected.push(id); } }"
                    :class="editMode && {{ $canDelete ? 'true' : 'false' }} ? 'cursor-pointer' : ''">
                    <div x-show="editMode" style="display: none;" class="relative z-20 self-center flex items-center" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                        <input type="checkbox" x-model="selected" value="{{ $notification->id }}" class="notif-checkbox h-4 w-4 rounded border-gray-300 text-usc-600 focus:ring-usc-600" {{ $canDelete ? '' : 'disabled' }} @click.stop>
                    </div>
                    <a href="{{ $href }}" x-show="!editMode" class="absolute inset-0 z-0"></a>
                    
                    <div class="relative z-10 self-center flex h-10 w-10 shrink-0 items-center justify-center rounded-full pointer-events-none {{ $isUnread ? 'bg-usc-100 text-usc-600' : 'bg-gray-100 text-gray-500' }}">
                        <x-nav-icon :name="$data['icon'] ?? 'bell'" class="h-5 w-5" />
                    </div>
                    <div class="relative z-10 min-w-0 flex-1 pointer-events-none">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-semibold {{ $isUnread ? 'text-usc-900' : 'text-gray-900' }}">
                                {{ $data['title'] ?? 'Notifikasi' }}
                                @if ($isUnread)
                                    <span class="relative ml-2 inline-flex h-2 w-2">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-usc-500 opacity-75"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-usc-500"></span>
                                    </span>
                                @endif
                            </p>
                            <span class="whitespace-nowrap text-xs font-medium {{ $isUnread ? 'text-usc-600' : 'text-gray-400' }}">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-0.5 text-sm {{ $isUnread ? 'text-gray-700' : 'text-gray-500' }}">{{ $data['detail'] ?? '' }}</p>
                        <div class="mt-2.5 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full {{ $isUnread ? 'bg-usc-100 text-usc-700' : 'bg-gray-100 text-gray-600' }} px-2 py-0.5 text-[11px] font-medium">
                                    {{ $data['category'] ?? 'Umum' }}
                                </span>
                                @if (!$canDelete)
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-600 ring-1 ring-inset ring-blue-500/20">
                                        Masih diproses
                                    </span>
                                @endif
                            </div>
                            <div class="relative z-20 pointer-events-auto flex items-center gap-2">
                                @if ($isUnread)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="rounded-md bg-white px-2.5 py-1 text-[11px] font-semibold text-usc-600 shadow-sm ring-1 ring-inset ring-usc-200 hover:bg-usc-50 focus:outline-none focus:ring-2 focus:ring-usc-500">
                                            Tandai dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-5 py-12 text-center text-sm text-gray-500">Belum ada notifikasi.</li>
            @endforelse
        </ul>

        <x-pagination :paginator="$notifications" />
        </div>
    </x-card>
</x-app-layout>
