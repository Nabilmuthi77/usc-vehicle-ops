@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'h-10 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10 shadow-sm']) }}>
