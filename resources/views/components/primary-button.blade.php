<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gradient-to-r from-usc-500 to-emerald-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-usc-500/20 transition-all duration-300 hover:-translate-y-1 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-usc-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
