@props(['from' => 1, 'to' => 10, 'total' => 10, 'page' => 1, 'lastPage' => 1])

<div class="flex flex-col items-center justify-between gap-3 border-t border-gray-100 px-4 py-3 sm:flex-row">
    <p class="text-sm text-gray-500">
        Menampilkan <span class="font-medium text-gray-700">{{ $from }}</span>
        &ndash; <span class="font-medium text-gray-700">{{ $to }}</span>
        dari <span class="font-medium text-gray-700">{{ $total }}</span> data
    </p>
    <div class="flex items-center gap-1">
        <button disabled class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm text-gray-400 disabled:cursor-not-allowed">
            &laquo; Sebelumnya
        </button>
        @for ($p = 1; $p <= $lastPage; $p++)
            <button class="min-w-[2.25rem] rounded-lg px-2.5 py-1.5 text-sm font-medium {{ $p === $page ? 'bg-usc-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ $p }}
            </button>
        @endfor
        <button class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm text-gray-600 hover:bg-gray-50">
            Berikutnya &raquo;
        </button>
    </div>
</div>
