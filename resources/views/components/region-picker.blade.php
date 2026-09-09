@props(['name' => 'destination', 'value' => ''])

<div x-data="{
        open: false,
        step: 'province', // 'province' or 'city'
        provinces: [],
        cities: [],
        selectedProvince: null,
        selectedCity: null,
        loading: false,
        value: '{{ addslashes((string) $value) }}',
        get label() {
            if (this.selectedCity && this.selectedProvince) {
                return this.selectedCity.name + ', ' + this.selectedProvince.name;
            }
            if (this.value) return this.value;
            return 'Pilih Tujuan';
        },
        init() {
            this.fetchProvinces();
        },
        fetchProvinces() {
            this.loading = true;
            fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
                .then(res => res.json())
                .then(data => {
                    this.provinces = data;
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loading = false;
                });
        },
        selectProvince(prov) {
            this.selectedProvince = prov;
            this.selectedCity = null;
            this.step = 'city';
            this.loading = true;
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${prov.id}.json`)
                .then(res => res.json())
                .then(data => {
                    this.cities = data;
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loading = false;
                });
        },
        selectCity(city) {
            this.selectedCity = city;
            this.value = city.name + ', ' + this.selectedProvince.name;
            this.open = false;
        },
        goBack(e) {
            e.stopPropagation();
            this.step = 'province';
            this.selectedCity = null;
            this.value = '';
        }
    }"
    class="relative w-full"
    @click.outside="open = false">

    <!-- Hidden Input for Form -->
    <input type="hidden" name="{{ $name }}" id="{{ $name }}" x-model="value">

    <!-- Trigger -->
    <button type="button" @click="open = !open"
        class="flex h-[34px] w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm text-[#02081C] outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50">
        <span x-text="label" class="truncate mr-2 text-left" x-bind:class="(!value && !selectedCity) ? 'text-gray-400 font-normal text-sm' : 'font-medium'"></span>
        <x-nav-icon name="chevron-down" class="h-4 w-4 text-gray-400 transition-transform duration-200 shrink-0" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <!-- Dropdown -->
    <div x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 w-full rounded-xl bg-white shadow-lg ring-1 ring-gray-900/5 overflow-hidden"
        style="display: none;">

        <div class="p-2 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <template x-if="step === 'city'">
                <button type="button" @click="goBack($event)" class="flex items-center text-xs font-medium text-usc-700 border border-usc-100 bg-white hover:bg-usc-50  px-2 py-1 rounded-lg
                transition duration-300
                ">
                    &lt;&lt;&lt; &nbsp; Kembali
                </button>
            </template>
            <template x-if="step === 'province'">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider pl-2">Pilih Provinsi</span>
            </template>
            <template x-if="step === 'city'">
                 <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider pr-2" x-text="selectedProvince?.name"></span>
            </template>

            <div x-show="loading" class="w-3 h-3 border-2 border-usc-400 border-t-transparent rounded-full animate-spin ml-2"></div>
        </div>

        <div class="max-h-60 overflow-y-auto p-1">
            <template x-if="step === 'province'">
                <ul>
                    <template x-for="prov in provinces" :key="prov.id">
                        <li>
                            <button type="button" @click="selectProvince(prov)" class="flex items-center justify-between w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-usc-50 hover:text-usc-700 rounded-lg transition-colors">
                                <span x-text="prov.name"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-400">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </li>
                    </template>
                </ul>
            </template>
            <template x-if="step === 'city'">
                <ul>
                    <template x-for="city in cities" :key="city.id">
                        <li>
                            <button type="button" @click="selectCity(city)" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-usc-50 hover:text-usc-700 rounded-lg transition-colors">
                                <span x-text="city.name"></span>
                            </button>
                        </li>
                    </template>
                    <template x-if="!loading && cities.length === 0">
                         <div class="px-3 py-2 text-sm text-gray-500 text-center">Tidak ada data kota</div>
                    </template>
                </ul>
            </template>
        </div>
    </div>
</div>
