<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edit Produk
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Perbarui informasi produk agar selalu akurat di etalase.
                </p>
            </div>

            <a href="{{ route('mitra.products.index') }}"
               class="inline-flex items-center px-3 py-1.5 text-xs rounded-lg border border-gray-300
                      bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                ← Kembali ke daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800/90 shadow-sm border border-emerald-50/70
                        dark:border-gray-700 sm:rounded-2xl overflow-hidden">
                <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">

                    {{-- Error validasi --}}
                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <div class="font-semibold mb-1">Ada kesalahan pada input:</div>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold mb-6">
                        {{ $produk->nama_produk }}
                    </h3>

                    <form action="{{ route('mitra.products.update', $produk->id) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- GRID 2 KOLOM: INFORMASI UTAMA --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Nama & kategori --}}
                            <div class="space-y-4">
                                <div>
                                    <label for="nama_produk"
                                           class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Nama Produk
                                    </label>
                                    <input type="text" name="nama_produk" id="nama_produk"
                                           value="{{ old('nama_produk', $produk->nama_produk) }}"
                                           class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700
                                                  dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                                  focus:ring-emerald-500 focus:border-emerald-500">
                                </div>

                                <div>
                                    <label for="kategori_produk"
                                           class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kategori Produk
                                    </label>
                                    <input type="text" name="kategori_produk" id="kategori_produk"
                                           value="{{ old('kategori_produk', $produk->kategori_produk) }}"
                                           class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700
                                                  dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                                  focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                            </div>

                            {{-- Deskripsi --}}
                            <div>
                                <label for="deskripsi_produk"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Deskripsi Produk
                                </label>
                                <textarea name="deskripsi_produk" id="deskripsi_produk" rows="6"
                                          class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700
                                                 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                                 focus:ring-emerald-500 focus:border-emerald-500"
                                >{{ old('deskripsi_produk', $produk->deskripsi_produk) }}</textarea>
                            </div>

                        </div>

                        {{-- GRID 3 KOLOM: HARGA, STOK, BERAT --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="harga"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Harga (Rp)
                                </label>
                                <input type="number" name="harga" id="harga" step="0.01" min="0"
                                       value="{{ old('harga', $produk->harga) }}"
                                       class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700
                                              dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                              focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="stok"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Stok
                                </label>
                                <input type="number" name="stok" id="stok" min="0"
                                       value="{{ old('stok', $produk->stok) }}"
                                       class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700
                                              dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                              focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="berat"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Berat (gram)
                                </label>
                                <input type="number" name="berat" id="berat" min="0"
                                       value="{{ old('berat', $produk->berat) }}"
                                       class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700
                                              dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                              focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        {{-- FOTO PRODUK --}}
                        @php
                            $fotoPath = $produk->foto_produk; // bisa: "storage/produk/xxx.jpg" atau "produk/xxx.jpg"
                            $fotoSrc = null;

                            if ($fotoPath) {
                                if (preg_match('/^https?:\/\//', $fotoPath)) {
                                    $fotoSrc = $fotoPath;
                                } elseif (\Illuminate\Support\Str::startsWith($fotoPath, 'storage/')) {
                                    // sudah ada prefix storage/
                                    $fotoSrc = asset($fotoPath);
                                } else {
                                    // format lama: "produk/xxx.jpg"
                                    $fotoSrc = asset('storage/' . $fotoPath);
                                }
                            }
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start">
                            @if ($fotoSrc)
                                <div>
                                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Foto Saat Ini
                                    </p>
                                    <img src="{{ $fotoSrc }}"
                                         alt="{{ $produk->nama_produk }}"
                                         class="h-32 w-32 object-cover rounded-xl border border-gray-200 dark:border-gray-700">
                                </div>
                            @endif

                            <div>
                                <label for="foto_produk"
                                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ganti Foto Produk
                                </label>
                                <input type="file" name="foto_produk" id="foto_produk"
                                       class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-100
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-emerald-50 file:text-emerald-700
                                              hover:file:bg-emerald-100">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Kosongkan jika tidak ingin mengganti. Format: jpg, jpeg, png.
                                </p>
                            </div>
                        </div>

                        {{-- Tombol submit --}}
                        <div class="pt-4 flex justify-end gap-3">
                            <a href="{{ route('mitra.products.index') }}"
                               class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300
                                      bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50
                                      dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-semibold
                                           bg-emerald-600 text-white shadow-sm hover:bg-emerald-700
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
