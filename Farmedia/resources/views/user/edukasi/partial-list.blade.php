@if($edukasies->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-300 p-6 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300">
        Tidak ada konten ditemukan. Coba ubah kata kunci atau kategori.
    </p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        @foreach($edukasies as $edukasi)
            <div class="group rounded-2xl overflow-hidden border border-emerald-100 dark:border-emerald-700 bg-white dark:bg-gray-900 shadow-sm hover:shadow-lg hover:border-emerald-300 transition">

                {{-- Header --}}
                <div class="px-5 pt-4 pb-3 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900 dark:to-emerald-800">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-gray-50 leading-snug line-clamp-2">
                        {{ $edukasi->judul }}
                    </h4>

                    <div class="mt-2 flex items-center justify-between text-xs">
                        @if($edukasi->kategori)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white/80 text-emerald-700 border border-emerald-200 text-[11px]">
                                🌱 {{ $edukasi->kategori }}
                            </span>
                        @endif
                        <span class="text-[11px] text-gray-500">
                            {{ $edukasi->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>

                {{-- Konten singkat --}}
                <div class="px-5 pt-4 pb-5 text-sm text-gray-700 dark:text-gray-300 line-clamp-4 min-h-[90px]">
                    {{ $edukasi->isi }}
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 flex items-center justify-between">
                    <span class="text-[11px] text-gray-400">
                        Baca selengkapnya untuk detail materi.
                    </span>
                    <a href="{{ route('user.edukasi.show', $edukasi->id) }}"
                       class="inline-flex items-center text-xs font-semibold text-emerald-600 group-hover:text-emerald-700">
                        Baca selengkapnya
                        <span class="ml-1">→</span>
                    </a>
                </div>

            </div>
        @endforeach

    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $edukasies->links() }}
    </div>
@endif
