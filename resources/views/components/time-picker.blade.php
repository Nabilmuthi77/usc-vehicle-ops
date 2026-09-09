@props(['name', 'id' => null, 'value' => ''])

@php
    $id = $id ?? $name;
@endphp

<div x-data="{
        open: false,
        time: '{{ $value }}',
        hour: '{{ $value ? explode(':', $value)[0] : '00' }}',
        minute: '{{ $value ? explode(':', $value)[1] : '00' }}',
        
        get display() {
            return this.time || '--:--';
        },
        
        updateTime() {
            this.time = this.hour.padStart(2, '0') + ':' + this.minute.padStart(2, '0');
            $refs.hiddenInput.value = this.time;
            $refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        },
        
        selectHour(h) {
            this.hour = h.toString();
            this.updateTime();
        },
        
        selectMinute(m) {
            this.minute = m.toString();
            this.updateTime();
        },
        
        scrollToActive() {
            let hTarget = $refs.hoursContainer.querySelector('.bg-usc-500');
            if (hTarget) {
                $refs.hoursContainer.scrollTop = hTarget.offsetTop - ($refs.hoursContainer.offsetHeight / 2) + (hTarget.offsetHeight / 2);
            }
            let mTarget = $refs.minutesContainer.querySelector('.bg-usc-500');
            if (mTarget) {
                $refs.minutesContainer.scrollTop = mTarget.offsetTop - ($refs.minutesContainer.offsetHeight / 2) + (mTarget.offsetHeight / 2);
            }
        }
    }"
    x-init="$watch('open', val => { 
        if(val) { 
            setTimeout(() => {
                this.scrollToActive();
            }, 10);
        } 
    })"
    class="relative w-full"
    @click.outside="open = false">
    
    <input type="hidden" id="{{ $id }}" name="{{ $name }}" x-ref="hiddenInput" x-model="time">
    
    <button type="button" @click="open = !open"
        class="flex h-[34px] w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-2 sm:px-4 text-sm text-[#02081C] outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50"
        :aria-expanded="open">
        
        <span x-text="display" class="font-medium text-sm text-center w-full" :class="time ? 'text-[#02081C]' : 'text-gray-400 font-normal'"></span>
    </button>
    
    <!-- Popover -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-[calc(100%+8px)] z-[60] w-[150px] sm:w-[160px] rounded-2xl border border-slate-200 bg-white p-2 sm:p-3 shadow-2xl shadow-slate-900/10"
         style="display: none;">
         
         <div class="flex gap-2 justify-center">
             <!-- Hours -->
             <div class="h-48 overflow-y-auto w-1/2 rounded-lg bg-slate-50 border border-slate-100 p-1 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
                  x-ref="hoursContainer">
                 @for ($i = 0; $i < 24; $i++)
                     <button type="button" 
                             @click="selectHour({{ $i }})"
                             class="w-full rounded-md py-1.5 text-center text-sm transition font-medium mb-0.5"
                             :class="hour == '{{ $i }}' ? 'bg-usc-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200'">
                         {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                     </button>
                 @endfor
             </div>
             
             <!-- Minutes -->
             <div class="h-48 overflow-y-auto w-1/2 rounded-lg bg-slate-50 border border-slate-100 p-1 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
                  x-ref="minutesContainer">
                 @for ($i = 0; $i < 60; $i++)
                     <button type="button" 
                             @click="selectMinute({{ $i }})"
                             class="w-full rounded-md py-1.5 text-center text-sm transition font-medium mb-0.5"
                             :class="minute == '{{ $i }}' ? 'bg-usc-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200'">
                         {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                     </button>
                 @endfor
             </div>
         </div>
         
         <button type="button" @click="open = false" class="mt-3 w-full rounded-xl bg-usc-50 py-2 text-xs font-semibold text-usc-600 hover:bg-usc-100 transition">
             Tutup
         </button>
    </div>
</div>
