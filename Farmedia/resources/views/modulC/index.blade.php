{{-- resources/views/admin/edukasi/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kelola Edukasi') }}
            </h2>

            <a href="{{ route('admin.edukasi.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                + Tambah Edukasi
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Card list edukasi --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Daftar Konten Edukasi
                    </h3>

                    @if($edukasies->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                            Belum ada data edukasi. Klik tombol <span class="font-semibold">"Tambah Edukasi"</span> di atas
                            untuk menambahkan konten baru.
                        </p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                            @foreach($edukasies as $edukasi)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm flex flex-col bg-white dark:bg-gray-900">
                                    {{-- Header card: Judul --}}
                                    <div class="px-4 pt-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $edukasi->judul }}
                                        </h4>
                                        @if(!empty($edukasi->kategori))
                                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[11px] bg-blue-100 text-blue-700">
                                                {{ $edukasi->kategori }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Bagian video / link --}}
                                    <div class="px-4 pt-3">
                                        @if($edukasi->link_video)
                                            <div class="mb-3">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                    Video edukasi:
                                                </p>

                                                @php
                                                    $url = $edukasi->link_video;
                                                    $isYoutube = \Illuminate\Support\Str::contains($url, ['youtube.com', 'youtu.be']);
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

                                                @if($isYoutube && $videoId)
                                                    <div class="w-full rounded-md overflow-hidden bg-black mb-2">
                                                        <iframe
                                                            class="w-full h-52 md:h-44"
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
                                                       class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 underline">
                                                        Buka video edukasi
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-400 italic">
                                                Tidak ada link video.
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Artikel / konten teks --}}
                                    <div class="px-4 pb-3 flex-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            Artikel:
                                        </p>
                                        <div class="text-sm text-gray-700 dark:text-gray-200 line-clamp-4">
                                            {{ $edukasi->isi ?? '-' }}
                                        </div>
                                    </div>

                                    {{-- Footer: info + tombol aksi --}}
                                    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                        <div class="text-[11px] text-gray-400">
                                            Dibuat: {{ $edukasi->created_at->format('d M Y') }}
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.edukasi.edit', $edukasi->id) }}"
                                               class="px-3 py-1 text-xs rounded-md bg-yellow-500 text-white hover:bg-yellow-600">
                                                Edit
                                            </a>

                                            <form action="{{ route('admin.edukasi.destroy', $edukasi->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus konten edukasi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1 text-xs rounded-md bg-red-600 text-white hover:bg-red-700">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4">
                            {{ $edukasies->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
