{{-- resources/views/user/edukasi/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edukasi Pertanian
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-emerald-100 dark:border-emerald-700 overflow-hidden">

                {{-- HEADER TITLE --}}
                <div class="px-6 py-5 bg-gradient-to-r from-emerald-700 via-emerald-600 to-emerald-500 text-white">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center">
                            📖
                        </div>
                        <div class="flex-1 space-y-1">
                            <h1 class="text-lg sm:text-xl font-semibold leading-snug">
                                {{ $edukasi->judul }}
                            </h1>

                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                @if($edukasi->kategori)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/15 text-emerald-50 border border-white/20">
                                        🌱 {{ $edukasi->kategori }}
                                    </span>
                                @endif

                                <span class="text-emerald-100">
                                    Dipublikasikan: {{ $edukasi->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="p-6 space-y-6">

                    {{-- Video --}}
                    @if($edukasi->link_video)
                        @php
                            $url = $edukasi->link_video;
                            $videoId = null;

                            if (\Illuminate\Support\Str::contains($url, 'watch?v=')) {
                                $videoId = explode('watch?v=', $url)[1];
                                $videoId = explode('&', $videoId)[0];
                            } elseif (\Illuminate\Support\Str::contains($url, 'youtu.be/')) {
                                $videoId = explode('youtu.be/', $url)[1];
                                $videoId = explode('?', $videoId)[0];
                            }
                        @endphp

                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">
                                Video Edukasi
                            </h3>

                            @if($videoId)
                                <div class="w-full rounded-xl overflow-hidden bg-black shadow-md">
                                    <iframe
                                        class="w-full h-64 md:h-80"
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
                                   class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 underline">
                                    Buka video edukasi di YouTube
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- Isi artikel --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">
                            Materi Edukasi
                        </h3>
                        <div class="mt-1 prose prose-sm max-w-none text-gray-800 dark:text-gray-100">
                            {!! nl2br(e($edukasi->isi)) !!}
                        </div>
                    </div>

                    {{-- Info kecil --}}
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="text-gray-400">
                            Terakhir diperbarui: {{ $edukasi->updated_at->format('d M Y, H:i') }}
                        </span>

                        <a href="{{ route('user.edukasi.index') }}"
                           class="inline-flex items-center text-emerald-600 hover:text-emerald-700 font-semibold">
                            ← Kembali ke daftar edukasi
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
