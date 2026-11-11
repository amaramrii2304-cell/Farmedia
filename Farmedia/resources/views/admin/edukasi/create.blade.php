{{-- resources/views/admin/edukasi/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
                Tambah Edukasi
            </h2>

            <a href="{{ route('admin.edukasi.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      border border-emerald-200 bg-white text-emerald-800 hover:bg-emerald-50 transition">
                <span class="text-base">←</span>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-50">
                <div class="p-6 sm:p-8">

                    {{-- ERROR VALIDASI --}}
                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <div class="font-semibold mb-1">Terjadi beberapa kesalahan:</div>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6">
                        <p class="text-xs uppercase tracking-[0.2em] text-emerald-600">
                            Form Edukasi Baru
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Buat konten edukasi berupa artikel dan/atau video untuk membantu petani Farmedia.
                        </p>
                    </div>

                    <form action="{{ route('admin.edukasi.store') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- JUDUL --}}
                        <div>
                            <label for="judul"
                                   class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                                Judul <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="judul"
                                id="judul"
                                value="{{ old('judul') }}"
                                required
                                class="mt-1 block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                            >
                        </div>

                        {{-- KATEGORI --}}
                        <div>
                            <label for="kategori"
                                   class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                                Kategori (opsional)
                            </label>
                            <input
                                type="text"
                                name="kategori"
                                id="kategori"
                                value="{{ old('kategori') }}"
                                placeholder="Contoh: Pupuk Organik, Hama, Panen, dll."
                                class="mt-1 block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                            >
                        </div>

                        {{-- LINK VIDEO --}}
                        <div>
                            <label for="link_video"
                                   class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                                Link Video (YouTube atau lainnya)
                            </label>
                            <input
                                type="text"
                                name="link_video"
                                id="link_video"
                                value="{{ old('link_video') }}"
                                placeholder="https://www.youtube.com/watch?v=..."
                                class="mt-1 block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                            >
                            <p class="mt-1 text-[11px] text-gray-500">
                                Boleh dikosongkan jika konten hanya berupa artikel.
                            </p>
                        </div>

                        {{-- ISI ARTIKEL --}}
                        <div>
                            <label for="isi"
                                   class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                                Artikel / Konten Edukasi
                            </label>
                            <textarea
                                name="isi"
                                id="isi"
                                rows="6"
                                class="mt-1 block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                                placeholder="Tulis materi edukasi di sini...">{{ old('isi') }}</textarea>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('admin.edukasi.index') }}"
                               class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-200 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50">
                                Batal
                            </a>

                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 rounded-xl text-xs font-semibold text-white
                                           bg-gradient-to-r from-emerald-500 to-emerald-600 shadow-md
                                           hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg transition">
                                Simpan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
