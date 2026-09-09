{{-- Pagination server-side (NFR Performa: daftar besar dipaginasi di server). --}}
@props(['paginator'])

@if ($paginator->hasPages())
    <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 px-5 py-4 sm:flex-row">
        <p class="text-sm text-gray-500">
            Menampilkan <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>
            &ndash; <span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
            dari <span class="font-medium text-gray-700">{{ $paginator->total() }}</span> data
        </p>
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button disabled class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-400 disabled:cursor-not-allowed">
                    &laquo; Sebelumnya
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-usc-50 hover:text-usc-700 hover:border-usc-200 transition-all duration-200">
                    &laquo; Sebelumnya
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($paginator->links()->elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-3 py-1.5 text-sm text-gray-500">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button disabled class="min-w-[2.25rem] rounded-lg bg-usc-50 px-3 py-1.5 text-sm font-semibold text-usc-700 shadow-sm disabled:cursor-default">
                                {{ $page }}
                            </button>
                        @else
                            <a href="{{ $url }}" class="min-w-[2.25rem] text-center rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-usc-100 hover:text-usc-800 transition-all duration-200">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-usc-50 hover:text-usc-700 hover:border-usc-200 transition-all duration-200">
                    Berikutnya &raquo;
                </a>
            @else
                <button disabled class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-400 disabled:cursor-not-allowed">
                    Berikutnya &raquo;
                </button>
            @endif
        </div>
    </div>
@endif
