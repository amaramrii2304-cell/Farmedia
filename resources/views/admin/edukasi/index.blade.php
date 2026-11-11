{{-- resources/views/admin/edukasi/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
                Kelola Edukasi
            </h2>

            <a href="{{ route('admin.edukasi.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-md
                      hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg transition">
                <span class="text-base">＋</span>
                <span>Tambah Edukasi</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- FLASH MESSAGE --}}
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-2">
                    <span class="text-lg mt-0.5">✔</span>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- CARD DAFTAR EDUKASI --}}
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-50">
                <div class="p-6 space-y-4">

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-emerald-900">
                                Daftar Konten Edukasi
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Kelola artikel dan video edukasi yang tampil ke pengguna.
                            </p>
                        </div>
                    </div>

                    @if($edukasies->isEmpty())
                        <div class="py-10 flex flex-col items-center justify-center text-center">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mb-3">
                                📚
                            </div>
                            <p class="text-sm text-gray-600">
                                Belum ada data edukasi.
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                Klik <span class="font-semibold">“Tambah Edukasi”</span> untuk membuat konten baru.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mt-2">
                            @foreach($edukasies as $edukasi)
                                @php
                                    $url = $edukasi->link_video;
                                    $isYoutube = $url && \Illuminate\Support\Str::contains($url, ['youtube.com', 'youtu.be']);
                                    $videoId = null;

                                    if ($isYoutube) {
                                        if (\Illuminate\Support\Str::contains($url, 'watch?v=')) {
                                            $videoId = explode('watch?v=', $url)[1];
                                            $videoId = explode('&', $videoId)[0];
                                        } elseif (\Illuminate\Support\Str::contains($url, 'youtu.be/')) {
                                            $videoId = explode('youtu.be/', $url)[1];
                                            $videoId = explode('?', $videoId)[0];
                                        }
                                    }
                                @endphp

                                <div class="group flex flex-col rounded-2xl border border-emerald-50 bg-emerald-50/40
                                            hover:bg-white shadow-sm hover:shadow-md transition overflow-hidden">

                                    {{-- HEADER --}}
                                    <div class="px-4 pt-4 pb-3 border-b border-emerald-50 flex flex-col gap-1">
                                        <h4 class="text-sm font-semibold text-gray-900 line-clamp-2">
                                            {{ $edukasi->judul }}
                                        </h4>

                                        <div class="flex items-center justify-between gap-2">
                                            @if(!empty($edukasi->kategori))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px]
                                                             bg-emerald-100 text-emerald-700 font-medium">
                                                    {{ $edukasi->kategori }}
                                                </span>
                                            @else
                                                <span class="text-[11px] text-gray-400 italic">
                                                    Tanpa kategori
                                                </span>
                                            @endif

                                            @if($edukasi->link_video)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px]
                                                             bg-emerald-600/10 text-emerald-700">
                                                    ▶
                                                    <span>Video</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- VIDEO (MINI) --}}
                                    <div class="px-4 pt-3">
                                        @if($edukasi->link_video)
                                            @if($isYoutube && $videoId)
                                                <div class="w-full rounded-xl overflow-hidden bg-black mb-2">
                                                    <iframe
                                                        class="w-full h-40"
                                                        src="https://www.youtube.com/embed/{{ $videoId }}"
                                                        title="Video edukasi"
                                                        frameborder="0"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                        referrerpolicy="strict-origin-when-cross-origin"
                                                        allowfullscreen>
                                                    </iframe>
                                                </div>
                                            @else
                                                <a href="{{ $edukasi->link_video }}" target="_blank"
                                                   class="inline-flex items-center text-[11px] text-emerald-700 hover:text-emerald-900 underline">
                                                    Buka video edukasi
                                                </a>
                                            @endif
                                        @else
                                            <p class="text-[11px] text-gray-400 italic">
                                                Tidak ada link video.
                                            </p>
                                        @endif
                                    </div>

                                    {{-- ISI SINGKAT --}}
                                    <div class="px-4 pb-3 flex-1">
                                        <p class="text-[11px] text-gray-400 mb-1">
                                            Ringkasan:
                                        </p>
                                        <p class="text-sm text-gray-700 line-clamp-4">
                                            {{ $edukasi->isi ?? '-' }}
                                        </p>
                                    </div>

                                    {{-- FOOTER --}}
                                    <div class="px-4 py-3 border-t border-emerald-50 flex items-center justify-between">
                                        <div class="text-[11px] text-gray-400">
                                            Dibuat: {{ $edukasi->created_at->format('d M Y') }}
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.edukasi.edit', $edukasi->id) }}"
                                               class="px-3 py-1 text-[11px] rounded-lg bg-amber-400/90 text-white font-semibold
                                                      hover:bg-amber-500 transition">
                                                Edit
                                            </a>

                                            <form action="{{ route('admin.edukasi.destroy', $edukasi->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus konten edukasi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1 text-[11px] rounded-lg bg-red-500 text-white font-semibold
                                                               hover:bg-red-600 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- PAGINATION --}}
                        <div class="mt-5">
                            {{ $edukasies->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
