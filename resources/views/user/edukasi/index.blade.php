<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edukasi Pertanian
        </h2>
    </x-slot>

    <div
        x-data="{
            q: '{{ request('q') }}',
            kategori: '',
            sort: 'terbaru',
            loading: false,
            loadData() {
                this.loading = true;

                fetch(`{{ route('user.edukasi.ajax') }}?q=${this.q}&kategori=${this.kategori}&sort=${this.sort}`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('ajax-edukasi-list').innerHTML = html;
                        this.loading = false;
                    });
            }
        }"
        class="py-10"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- FILTER BAR / SEARCH --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl border border-emerald-100 overflow-hidden">
                {{-- strip hijau atas --}}
                <div class="h-1.5 bg-gradient-to-r from-emerald-700 via-emerald-500 to-emerald-400"></div>

                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                            📚
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                Cari Materi Edukasi
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Temukan artikel, tips, dan panduan budidaya pertanian.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Search Input --}}
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-300">Pencarian</label>
                            <div class="relative mt-1">
                                <input
                                    x-model="q"
                                    @input.debounce.400ms="loadData()"
                                    type="text"
                                    placeholder="Cari judul, kategori, isi..."
                                    class="w-full rounded-xl py-2.5 border border-emerald-200 dark:border-emerald-600 dark:bg-gray-900 dark:text-gray-200 pl-11 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                                >
                                <span class="absolute left-3 top-2.5 text-emerald-500 text-lg">🔍</span>
                            </div>
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-300">Kategori</label>
                            <select
                                x-model="kategori"
                                @change="loadData()"
                                class="w-full rounded-xl mt-1 py-2.5 border border-emerald-200 dark:border-emerald-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                            >
                                <option value="">Semua Kategori</option>
                                <option value="Tanaman">Tanaman</option>
                                <option value="Pupuk">Pupuk</option>
                                <option value="Alat Pertanian">Alat Pertanian</option>
                                <option value="Teknik Budidaya">Teknik Budidaya</option>
                            </select>
                        </div>

                        {{-- Sort --}}
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-300">Urutkan</label>
                            <select
                                x-model="sort"
                                @change="loadData()"
                                class="w-full rounded-xl mt-1 py-2.5 border border-emerald-200 dark:border-emerald-600 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                            >
                                <option value="terbaru">Terbaru</option>
                                <option value="terlama">Terlama</option>
                                <option value="az">Judul A - Z</option>
                                <option value="za">Judul Z - A</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- LOADING --}}
            <div x-show="loading" class="text-center text-sm text-gray-500 dark:text-gray-300">
                Memuat data edukasi...
            </div>

            {{-- AJAX CONTENT --}}
            <div id="ajax-edukasi-list">
                @include('user.edukasi.partial-list', ['edukasies' => $edukasies])
            </div>

        </div>
    </div>
</x-app-layout>
