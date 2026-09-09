<x-app-layout title="Biaya Tol">
    <x-slot name="header">
        <x-page-header title="Biaya Tol" subtitle="Saldo kartu e-toll dan riwayat transaksi" :crumbs="['Operasional', 'Tol']">
            <x-slot name="actions">
                @can('create', \App\Models\TollTransaction::class)
                    <div class="flex-1 sm:flex-none relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" 
                            class="inline-flex w-full justify-center items-center gap-1.5 rounded-lg bg-white px-2 sm:px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-usc-700 hover:bg-usc-50 transition">
                            <x-nav-icon name="credit-card" class="h-4 w-4 shrink-0" />
                            <span class="truncate">Kelola Kartu</span>
                        </button>
                        
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                            class="absolute left-0 sm:left-auto sm:right-0 z-30 mt-2 w-56 origin-top-left sm:origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-gray-900/5 p-1"
                            style="display: none;"
                        >
                            <button x-on:click.prevent="open = false; $dispatch('open-modal', 'modal-add-card')" class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                                Registrasi Kartu Baru
                            </button>
                            
                            <div class="my-1 border-t border-gray-100"></div>
                            
                            <button x-on:click.prevent="open = false; $dispatch('open-modal', 'modal-topup')" class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                                Top-up Saldo
                            </button>
                            
                            <button x-on:click.prevent="open = false; $dispatch('open-modal', 'modal-reconcile')" class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                                Rekonsiliasi Saldo
                            </button>
                        </div>
                    </div>

                    <x-btn-primary icon="plus" class="flex-1 sm:flex-none justify-center px-2 sm:px-3.5" x-data="" x-on:click.prevent="$dispatch('open-modal', 'modal-add-transaction')">
                        <span class="truncate">Catat Transaksi</span>
                    </x-btn-primary>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $statusOpts = [];
        foreach(\App\Enums\TollCardStatus::cases() as $status) {
            $statusOpts[$status->value] = $status->label();
        }
        $vehicleOptsModal = ['' => 'Tanpa Kendaraan (Standby / Pool)'];
        foreach($vehicles as $vehicle) {
            $vehicleOptsModal[$vehicle->id] = $vehicle->plate_number;
        }
        $cardOptsModal = ['' => '-- Pilih Kartu --'];
        foreach($cards as $c) {
            $cardOptsModal[$c->id] = $c->issuer . ' - ' . ($c->vehicle?->plate_number ?? 'Tanpa Kendaraan');
        }
        $transVehicleOpts = ['' => '-- Pilih Kendaraan --'];
        foreach($vehicles as $v) {
            $transVehicleOpts[$v->id] = $v->plate_number;
        }
        $vehicleClassOpts = ['' => 'Pilih Golongan...'];
        foreach($vehicleClasses as $val => $label) {
            $vehicleClassOpts[$val] = $label;
        }
    @endphp


    {{-- FR-M5-01 & FR-M5-07 — saldo kartu beserta peringatan ambang minimum. --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <x-card>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $card->issuer }}</p>
                        <p class="text-xs text-gray-500">{{ $card->vehicle?->plate_number ?? 'Belum ditempel' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-status-badge :status="$card->status" />
                        @can('create', \App\Models\TollTransaction::class)
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <x-nav-icon name="dots" class="h-4 w-4" />
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link x-data="" x-on:click.prevent="$dispatch('open-modal', 'modal-edit-card-{{ $card->id }}')">
                                    Edit Kartu
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                        @endcan
                    </div>
                </div>
                <p class="mt-3 text-xl font-semibold tabular-nums {{ $card->isBelowMinimum() ? 'text-red-600' : 'text-gray-900' }}">
                    Rp {{ number_format((float) $card->balance, 0, ',', '.') }}
                </p>
                @if ($card->isBelowMinimum())
                    <p class="mt-1 text-[11px] font-medium text-red-600">
                        Di bawah ambang Rp {{ number_format((float) $card->min_balance_alert, 0, ',', '.') }}
                    </p>
                @endif
                <p class="mt-1 text-[11px] text-gray-400">{{ $card->card_number }}</p>
                @if ($card->notes)
                    <div class="mt-3 border-t border-gray-100 pt-2">
                        <p class="text-[11px] italic text-gray-500 line-clamp-2" title="{{ $card->notes }}">
                            "{{ $card->notes }}"
                        </p>
                    </div>
                @endif
            </x-card>
            
            @can('create', \App\Models\TollTransaction::class)
            <x-modal name="modal-edit-card-{{ $card->id }}" :show="$errors->hasBag('updateCard') && old('card_id') == $card->id" maxWidth="md">
                <form method="POST" action="{{ route('toll.cards.update', $card) }}" class="p-6" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="card_id" value="{{ $card->id }}">
                    
                    <h2 class="text-lg font-medium text-gray-900">
                        Edit Kartu E-Toll ({{ $card->card_number }})
                    </h2>
                    
                    @if($errors->hasBag('updateCard') && old('card_id') == $card->id)
                        <div class="mt-4"><x-alert bag="updateCard" /></div>
                    @endif
                    
                    <div class="mt-4 space-y-4">
                        <div>
                            <x-input-label for="status_{{ $card->id }}" value="Status Kartu" />
                            <div class="mt-1">
                                <x-filter-select name="status" id="status_{{ $card->id }}" :options="$statusOpts" :selected="$card->status->value" containerClass="w-full" class="border-slate-200" required />
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="vehicle_id_{{ $card->id }}" value="Kendaraan (Opsional)" />
                            <div class="mt-1">
                                <x-filter-select name="vehicle_id" id="vehicle_id_{{ $card->id }}" :options="$vehicleOptsModal" :selected="$card->vehicle_id" containerClass="w-full" class="border-slate-200" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="notes_{{ $card->id }}" value="Catatan Tambahan (Opsional)" />
                            <textarea id="notes_{{ $card->id }}" name="notes" rows="2" class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" placeholder="Contoh: Disimpan di laci admin">{{ $card->notes }}</textarea>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">Batal</x-secondary-button>
                        <x-primary-button class="w-full sm:w-auto justify-center">Simpan Perubahan</x-primary-button>
                    </div>
                </form>
            </x-modal>
            @endcan
        @endforeach
    </div>

    <div x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'transactions' }">
        <div class="mb-4 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'transactions'" :class="activeTab === 'transactions' ? 'border-usc-500 text-usc-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">Transaksi Tol</button>
                <button @click="activeTab = 'topups'" :class="activeTab === 'topups' ? 'border-usc-500 text-usc-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">Riwayat Top-up</button>
                <button @click="activeTab = 'adjustments'" :class="activeTab === 'adjustments' ? 'border-usc-500 text-usc-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">Rekonsiliasi</button>
            </nav>
        </div>

        <div x-show="activeTab === 'transactions'">
            <x-card :padded="false">
        <form method="GET" action="{{ route('toll.index') }}">
            <input type="hidden" name="tab" value="transactions">
            <x-filter-bar searchPlaceholder="Gunakan filter di samping..." name="q" :value="$filters['q'] ?? ''">
                <x-filter-select name="vehicle_id"
                                 :options="['' => 'Semua Kendaraan'] + $vehicles->pluck('plate_number', 'id')->all()"
                                 :selected="$filters['vehicle_id'] ?? ''" />
                <x-filter-select name="toll_card_id"
                                 :options="['' => 'Semua Kartu'] + $cards->pluck('issuer', 'id')->all()"
                                 :selected="$filters['toll_card_id'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Kartu</th>
                        <th class="px-5 py-3">Gerbang</th>
                        <th class="px-5 py-3">Ruas</th>
                        <th class="px-5 py-3">Gol.</th>
                        <th class="px-5 py-3 text-right">Tarif</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $t)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $t->transaction_datetime->format('d-m-Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">
                                {{ $t->vehicle?->plate_number }}
                                @if ($t->booking)
                                    {{-- FR-M5-04 — biaya dibebankan ke peminjaman. --}}
                                    <p class="text-[11px] font-normal text-gray-400">{{ $t->booking->booking_number }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $t->tollCard?->issuer }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $t->entry_gate }} &rarr; {{ $t->exit_gate }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $t->road_section }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $t->vehicle_class->value }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums font-medium text-gray-900">
                                Rp {{ number_format((float) $t->amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                @can('delete', $t)
                                    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-delete-modal', '{{ route('toll.transactions.destroy', $t) }}')" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <x-nav-icon name="trash" class="h-4 w-4" />
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="8" message="Belum ada transaksi tol." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$transactions" />
    </x-card>
        </div>

        <div x-show="activeTab === 'topups'" x-cloak>
            <x-card :padded="false">
                <form method="GET" action="{{ route('toll.index') }}">
                    <input type="hidden" name="tab" value="topups">
                    <x-filter-bar searchPlaceholder="Cari catatan top-up..." name="q" :value="$filters['q'] ?? ''">
                        <x-filter-select name="toll_card_id"
                                         :options="['' => 'Semua Kartu'] + $cards->pluck('issuer', 'id')->all()"
                                         :selected="$filters['toll_card_id'] ?? ''" />
                    </x-filter-bar>
                </form>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-white/80 backdrop-blur">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Kartu</th>
                                <th class="px-5 py-3">Kendaraan</th>
                                <th class="px-5 py-3">Metode/Catatan</th>
                                <th class="px-5 py-3">Oleh</th>
                                <th class="px-5 py-3 text-right">Nominal</th>
                                <th class="px-5 py-3 text-center">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($topups as $t)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $t->topup_date->format('d-m-Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-900 font-medium">{{ $t->tollCard?->issuer }} - {{ $t->tollCard?->card_number }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $t->tollCard?->vehicle?->plate_number ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-gray-600">
                                        <div class="font-medium text-gray-900">{{ $t->method ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $t->notes }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $t->createdBy?->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right font-medium text-emerald-600 tabular-nums">
                                        + Rp {{ number_format((float) $t->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                        @if ($t->proof_path)
                                            <button type="button" x-data="" x-on:click.prevent="$dispatch('open-proof-modal', '{{ Storage::url($t->proof_path) }}')" class="text-usc-600 hover:text-usc-800 text-xs font-medium">Lihat Bukti</button>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-empty-row :colspan="7" message="Belum ada riwayat top-up." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$topups" />
            </x-card>
        </div>

        <div x-show="activeTab === 'adjustments'" x-cloak>
            <x-card :padded="false">
                <form method="GET" action="{{ route('toll.index') }}">
                    <input type="hidden" name="tab" value="adjustments">
                    <x-filter-bar searchPlaceholder="Cari alasan rekonsiliasi..." name="q" :value="$filters['q'] ?? ''">
                        <x-filter-select name="toll_card_id"
                                         :options="['' => 'Semua Kartu'] + $cards->pluck('issuer', 'id')->all()"
                                         :selected="$filters['toll_card_id'] ?? ''" />
                    </x-filter-bar>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-white/80 backdrop-blur">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Kartu</th>
                                <th class="px-5 py-3 text-right">Saldo Sistem</th>
                                <th class="px-5 py-3 text-right">Saldo Aktual</th>
                                <th class="px-5 py-3 text-right">Selisih</th>
                                <th class="px-5 py-3">Alasan</th>
                                <th class="px-5 py-3">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($adjustments as $a)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $a->adjustment_date->format('d-m-Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-900 font-medium">{{ $a->tollCard?->issuer }} - {{ $a->tollCard?->card_number }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right text-gray-600 tabular-nums">Rp {{ number_format((float) $a->system_balance, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right font-medium text-gray-900 tabular-nums">Rp {{ number_format((float) $a->actual_balance, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right font-medium tabular-nums {{ $a->amount < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        {{ $a->amount > 0 ? '+' : '' }} Rp {{ number_format((float) $a->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $a->reason }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $a->createdBy?->name }}</td>
                                </tr>
                            @empty
                                <x-empty-row :colspan="7" message="Belum ada riwayat rekonsiliasi." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$adjustments" />
            </x-card>
        </div>
    </div>

@can('create', \App\Models\TollTransaction::class)
    {{-- Modal: Tambah Transaksi --}}
    <x-modal name="modal-add-transaction" :show="$errors->hasBag('storeTransaction')" maxWidth="2xl">
        <form method="POST" action="{{ route('toll.transactions.store') }}" class="p-6" novalidate x-data="{
            tollCardId: '',
            vehicleId: '',
            entryGate: '',
            exitGate: '',
            vehicleClass: '',
            amount: '',
            roadSection: '',
            isLoading: false,
            allBookings: @js($activeBookings),
            filteredBookings: [],
            cards: @js($cards->map(fn($c) => ['id' => $c->id, 'vehicle_id' => $c->vehicle_id])),
            classDescriptions: {
                'I': 'Sedan, Jip, Pick Up/Truk Kecil, dan Bus',
                'II': 'Truk dengan 2 (dua) gandar',
                'III': 'Truk dengan 3 (tiga) gandar',
                'IV': 'Truk dengan 4 (empat) gandar',
                'V': 'Truk dengan 5 (lima) gandar',
                'VI': 'Kendaraan bermotor roda 2 (dua)'
            },
            
            init() {
                this.$watch('tollCardId', (val) => {
                    const card = this.cards.find(c => c.id == val);
                    if (card && card.vehicle_id) {
                        this.vehicleId = card.vehicle_id;
                    } else {
                        this.vehicleId = ''; // Wajib pilih kendaraan
                    }
                });

                this.$watch('vehicleId', (val) => {
                    if (val) {
                        this.filteredBookings = this.allBookings.filter(b => b.vehicle_id == val);
                    } else {
                        this.filteredBookings = [];
                    }
                });
            },
            
            async lookupRate() {
                if (!this.entryGate || !this.exitGate || !this.vehicleClass) return;
                this.isLoading = true;
                try {
                    const res = await fetch(`/tol/tarif?entry_gate=${encodeURIComponent(this.entryGate)}&exit_gate=${encodeURIComponent(this.exitGate)}&vehicle_class=${this.vehicleClass}`);
                    const data = await res.json();
                    if (data.found && data.amount) {
                        this.amount = data.amount;
                        this.roadSection = data.road_section || '';
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.isLoading = false;
                }
            }
        }">
            @csrf
            <input type="hidden" name="road_section" x-model="roadSection">
            <h2 class="text-lg font-medium text-gray-900">Catat Transaksi Tol</h2>
            <p class="mt-1 text-sm text-gray-600">Saldo kartu akan otomatis berkurang sesuai nominal transaksi.</p>
            
            @if($errors->hasBag('storeTransaction'))
                <div class="mt-4"><x-alert bag="storeTransaction" /></div>
            @endif
            
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="toll_card_id" value="Kartu E-Toll" />
                    <div class="mt-1">
                        <x-filter-select name="toll_card_id" id="toll_card_id" :options="$cardOptsModal" :selected="old('toll_card_id')" containerClass="w-full" class="border-slate-200" @change="tollCardId = $event.target.value" required />
                    </div>
                </div>
                <div>
                    <x-input-label for="trans_vehicle_id" value="Kendaraan" />
                    <div class="mt-1">
                        <x-filter-select name="vehicle_id" id="trans_vehicle_id" :options="$transVehicleOpts" :selected="old('vehicle_id')" containerClass="w-full" class="border-slate-200" @change="vehicleId = $event.target.value" required />
                    </div>
                </div>
                <div x-data="{ tDate: '{{ now()->format('Y-m-d') }}', tTime: '{{ now()->format('H:i') }}' }">
                    <x-input-label value="Waktu Transaksi" />
                    <div class="mt-1 flex gap-2 w-full">
                        <div class="flex-1" @change="tDate = $event.target.value">
                            <x-date-picker id="trans_date" name="trans_date" :value="now()->format('Y-m-d')" placeholder="Tanggal" />
                        </div>
                        <div class="w-[72px] sm:w-[76px] shrink-0" @change="tTime = $event.target.value">
                            <x-time-picker id="trans_time" name="trans_time" :value="now()->format('H:i')" />
                        </div>
                    </div>
                    <input type="hidden" name="transaction_datetime" x-bind:value="tDate + ' ' + tTime">
                </div>
                <div>
                    <x-input-label for="booking_id" value="Peminjaman (Opsional)" />
                    <div x-data="{
                            open: false,
                            value: '',
                            get label() {
                                if (!this.value) return '-- Tidak Terkait --';
                                const b = this.filteredBookings.find(bk => bk.id == this.value);
                                return b ? b.booking_number : '-- Tidak Terkait --';
                            }
                        }"
                        x-init="$watch('filteredBookings', () => value = '')"
                        class="relative w-full mt-1"
                        @click.outside="open = false">

                        <select x-model="value" id="booking_id" name="booking_id" class="hidden">
                            <option value="">-- Tidak Terkait --</option>
                            <template x-for="b in filteredBookings" :key="b.id">
                                <option x-bind:value="b.id" x-text="b.booking_number"></option>
                            </template>
                        </select>

                        <button type="button" @click="open = !open"
                            class="flex h-[34px] w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
                            <span x-text="label" class="truncate mr-2" x-bind:class="!value ? 'text-gray-400 font-normal text-sm' : 'font-medium'"></span>
                            <x-nav-icon name="chevron-down" class="h-4 w-4 text-gray-400 transition-transform duration-200 shrink-0" x-bind:class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-2 w-full rounded-xl bg-white shadow-lg ring-1 ring-gray-900/5 py-1.5 max-h-60 overflow-y-auto"
                            style="display: none;">
                            
                            <button type="button" @click="value = ''; open = false;"
                                class="flex w-full items-center justify-between px-4 py-2 text-sm text-left transition-colors focus:outline-none"
                                x-bind:class="value === '' ? 'bg-usc-50 text-usc-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'">
                                <span class="truncate">-- Tidak Terkait --</span>
                                <span x-show="value === ''" class="shrink-0 ml-2">
                                    <x-nav-icon name="check-circle" class="h-4 w-4 text-usc-600" />
                                </span>
                            </button>

                            <template x-for="b in filteredBookings" :key="b.id">
                                <button type="button" @click="value = b.id; open = false;"
                                    class="flex w-full items-center justify-between px-4 py-2 text-sm text-left transition-colors focus:outline-none"
                                    x-bind:class="value == b.id ? 'bg-usc-50 text-usc-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'">
                                    <span class="truncate" x-text="b.booking_number"></span>
                                    <span x-show="value == b.id" style="display: none;" class="shrink-0 ml-2">
                                        <x-nav-icon name="check-circle" class="h-4 w-4 text-usc-600" />
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500" x-show="!vehicleId">Pilih kendaraan terlebih dahulu.</p>
                </div>

                <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase">Tarif & Rute</h3>
                </div>

                <div>
                    <x-input-label for="entry_gate" value="Gerbang Masuk" />
                    <input id="entry_gate" name="entry_gate" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" x-model="entryGate" @change="lookupRate" />
                </div>
                <div>
                    <x-input-label for="exit_gate" value="Gerbang Keluar" />
                    <input id="exit_gate" name="exit_gate" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" x-model="exitGate" @change="lookupRate" />
                </div>
                <div>
                    <x-input-label for="vehicle_class" value="Golongan Kendaraan" />
                    <div class="mt-1">
                        <x-filter-select name="vehicle_class" id="vehicle_class" :options="$vehicleClassOpts" :selected="old('vehicle_class')" containerClass="w-full" class="border-slate-200" @change="vehicleClass = $event.target.value; lookupRate()" required />
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500">
                        <span x-show="!vehicleClass">Pilih golongan untuk melihat detail kendaraan.</span>
                        <span x-show="vehicleClass" x-text="classDescriptions[vehicleClass] ? 'Termasuk: ' + classDescriptions[vehicleClass] : ''"></span>
                    </p>
                </div>
                <div>
                    <x-input-label for="amount" value="Nominal Tarif (Rp)" />
                    <div class="relative mt-1">
                        <input id="amount" name="amount" type="number" step="1" class="block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50" x-model="amount" required />
                        <div x-show="isLoading" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-usc-500">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">Batal</x-secondary-button>
                <x-primary-button class="w-full sm:w-auto justify-center">Simpan Transaksi</x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: Topup --}}
    <x-modal name="modal-topup" :show="$errors->hasBag('topup')" maxWidth="md">
        <div x-data="{
            cardId: ''
        }">
            <form method="POST" action="{{ route('toll.topup') }}" class="p-6" enctype="multipart/form-data" novalidate>
                @csrf
                <h2 class="text-lg font-medium text-gray-900">Top-up Kartu E-Toll</h2>
                <p class="mt-1 text-sm text-gray-600">Catat penambahan saldo ke kartu.</p>
                
                @if($errors->hasBag('topup'))
                    <div class="mt-4"><x-alert bag="topup" /></div>
                @endif
                
                <div class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="topup_card_id" value="Pilih Kartu" />
                        <div class="mt-1">
                            <x-filter-select name="toll_card_id" id="topup_card_id" :options="$cardOptsModal" :selected="old('toll_card_id')" containerClass="w-full" class="border-slate-200" @change="cardId = $event.detail" required />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="topup_date" value="Tanggal Top-up" />
                        <div class="mt-1">
                            <x-date-picker id="topup_date" name="topup_date" :value="now()->format('Y-m-d')" placeholder="Pilih tanggal top-up" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="topup_amount" value="Nominal Top-up (Rp)" />
                        <input id="topup_amount" name="amount" type="number" step="1" min="1" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50" required />
                    </div>
                    <div>
                        <x-input-label for="topup_method" value="Metode/Sumber Dana (Opsional)" />
                        <input id="topup_method" name="method" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" placeholder="Contoh: Transfer Bank, Petty Cash" />
                    </div>
                    <div>
                        <x-input-label for="topup_notes" value="Catatan Tambahan (Opsional)" />
                        <textarea id="topup_notes" name="notes" rows="2" class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"></textarea>
                    </div>
                    <div>
                        <x-input-label for="topup_proof" value="Bukti Top-up (Opsional)" />
                        <input type="file" id="topup_proof" name="proof" accept="image/*,.pdf" class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-usc-50 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-usc-700 hover:file:bg-usc-100" />
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">Batal</x-secondary-button>
                    <x-primary-button class="w-full sm:w-auto justify-center">Simpan Top-up</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Modal: Rekonsiliasi --}}
    <x-modal name="modal-reconcile" :show="$errors->hasBag('reconcile')" maxWidth="md">
        <div x-data="{
            cardId: ''
        }">
            <form method="POST" action="{{ route('toll.reconcile') }}" class="p-6" novalidate>
                @csrf
                <h2 class="text-lg font-medium text-gray-900">Rekonsiliasi Saldo</h2>
                <p class="mt-1 text-sm text-gray-600">Sesuaikan saldo sistem dengan saldo aktual di kartu fisik.</p>
                
                @if($errors->hasBag('reconcile'))
                    <div class="mt-4"><x-alert bag="reconcile" /></div>
                @endif
                
                <div class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="recon_card_id" value="Pilih Kartu" />
                        <div class="mt-1">
                            <x-filter-select name="toll_card_id" id="recon_card_id" :options="$cardOptsModal" :selected="old('toll_card_id')" containerClass="w-full" class="border-slate-200" @change="cardId = $event.detail" required />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="actual_balance" value="Saldo Aktual di Kartu Fisik (Rp)" />
                        <input id="actual_balance" name="actual_balance" type="number" step="1" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50" required />
                    </div>
                    <div>
                        <x-input-label for="reason" value="Alasan Penyesuaian" />
                        <textarea id="reason" name="reason" rows="2" class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" placeholder="Contoh: Transaksi tol tidak tercatat oleh driver" required></textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">Batal</x-secondary-button>
                    <x-primary-button class="w-full sm:w-auto justify-center">Proses Rekonsiliasi</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Modal: Registrasi Kartu Baru --}}
    @php
        $hasAddCardErrors = $errors->hasBag('storeCard');
    @endphp
    <x-modal name="modal-add-card" :show="$hasAddCardErrors" maxWidth="md">
        <form method="POST" action="{{ route('toll.cards.store') }}" class="p-6" novalidate>
            @csrf
            <h2 class="text-lg font-medium text-gray-900">Registrasi Kartu E-Toll Baru</h2>
            <p class="mt-1 text-sm text-gray-600">Daftarkan fisik kartu e-toll baru ke dalam sistem.</p>
            
            @if($hasAddCardErrors)
                <div class="mt-4"><x-alert bag="storeCard" /></div>
            @endif
            
            <div class="mt-4 space-y-4">
                <div>
                    <x-input-label for="new_card_number" value="Nomor Kartu" />
                    <input id="new_card_number" name="card_number" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50" required />
                </div>
                <div>
                    <x-input-label for="new_issuer" value="Penerbit (Issuer)" />
                    <input id="new_issuer" name="issuer" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" placeholder="Contoh: Mandiri E-Money, BCA Flazz" required />
                </div>
                <div>
                    <x-input-label for="new_vehicle_id" value="Ditempel pada Kendaraan (Opsional)" />
                    <div class="mt-1">
                        <x-filter-select name="vehicle_id" id="new_vehicle_id" :options="$vehicleOptsModal" :selected="old('vehicle_id')" containerClass="w-full" class="border-slate-200" />
                    </div>
                </div>
                <div>
                    <x-input-label for="new_balance" value="Saldo Awal (Rp)" />
                    <input id="new_balance" name="balance" type="number" step="1" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50" required />
                </div>
                <div>
                    <x-input-label for="new_min_balance" value="Peringatan Saldo Minimum (Rp)" />
                    <input id="new_min_balance" name="min_balance_alert" type="number" step="1" min="0" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50" value="50000" required />
                </div>
                <div>
                    <x-input-label for="new_notes" value="Catatan Tambahan (Opsional)" />
                    <input id="new_notes" name="notes" type="text" class="mt-1 block w-full h-[34px] rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal" />
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">Batal</x-secondary-button>
                <x-primary-button class="w-full sm:w-auto justify-center">Simpan Kartu</x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: Hapus Transaksi --}}
    <div x-data="{ deleteUrl: '' }" x-on:open-delete-modal.window="deleteUrl = $event.detail; $dispatch('open-modal', 'modal-delete-transaction')">
        <x-modal name="modal-delete-transaction" maxWidth="sm">
            <form x-bind:action="deleteUrl" method="POST" class="p-6">
                @csrf
                @method('DELETE')
                <div class="flex items-center gap-3 text-red-600">
                    <x-nav-icon name="trash" class="h-6 w-6" />
                    <h2 class="text-lg font-medium text-gray-900">Hapus Transaksi</h2>
                </div>
                <p class="mt-3 text-sm text-gray-600">Yakin ingin menghapus transaksi tol ini? Saldo kartu akan otomatis dihitung ulang.</p>
                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')" class="w-full sm:w-auto justify-center">Batal</x-secondary-button>
                    <x-danger-button type="submit" class="w-full sm:w-auto justify-center">Ya, Hapus</x-danger-button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Modal: Lihat Bukti Top-up (Viewer.js) --}}
    @once
    <style>
        /* USC Theme Override for Viewer.js */
        .viewer-container { background-color: rgba(0, 0, 0, 0.2) !important; backdrop-filter: blur(4px); }
        .viewer-button { background-color: #ffffffff !important; width: 44px !important; height: 44px !important; right: 24px !important; top: 24px !important; border-radius: 50% !important; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important; }
        .viewer-button:hover { background-color: #ecfdf5 !important; transform: scale(1.1) rotate(90deg) !important; }
        .viewer-button::before, .viewer-button::after { content: '' !important; position: absolute !important; top: 50% !important; left: 50% !important; width: 20px !important; height: 3px !important; background-color: #64748b !important; border-radius: 2px !important; background-image: none !important; filter: none !important; }
        .viewer-button::before { transform: translate(-50%, -50%) rotate(45deg) !important; }
        .viewer-button::after { transform: translate(-50%, -50%) rotate(-45deg) !important; }
        .viewer-toolbar > ul > li { background-color: #ffffff !important; width: 40px !important; height: 40px !important; border-radius: 50% !important; margin: 0 6px !important; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1) !important; }
        .viewer-toolbar > ul > li:hover { background-color: #ecfdf5 !important; transform: translateY(-4px) scale(1.05) !important; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important; }
        .viewer-toolbar > ul > li::before { margin: 10px !important; filter: invert(1) opacity(0.6) !important; }
    </style>
    @endonce
    <div x-data="{
        viewer: null,
        openViewer(url) {
            if (typeof Viewer === 'undefined') {
                let script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js';
                let link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css';
                script.onload = () => { this.initViewer(url); };
                document.head.appendChild(link);
                document.head.appendChild(script);
            } else {
                this.initViewer(url);
            }
        },
        initViewer(url) {
            if (this.viewer) {
                this.viewer.destroy();
            }
            let img = document.createElement('img');
            img.src = url;
            this.viewer = new Viewer(img, {
                hidden: () => { this.viewer.destroy(); this.viewer = null; },
                button: true, navbar: false, title: false,
                toolbar: { zoomIn: 1, zoomOut: 1, oneToOne: 1, reset: 1, rotateLeft: 1, rotateRight: 1 },
                backdrop: true,
            });
            this.viewer.show();
        }
    }" @open-proof-modal.window="openViewer($event.detail)">
    </div>
@endcan
</x-app-layout>

