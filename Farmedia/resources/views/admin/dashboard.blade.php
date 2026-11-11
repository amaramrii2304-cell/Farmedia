{{-- resources/views/admin/dashboard.blade.php --}}
<x-app-layout>
    <div class="space-y-6">

        {{-- HEADER KECIL --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">
                    Ringkasan Sistem
                </h1>
                <p class="text-xs text-gray-500">
                    Overview singkat aktivitas di Farmedia.
                </p>
            </div>
            <div class="text-xs text-gray-400">
                {{ now()->format('d M Y') }}
            </div>
        </div>

        {{-- STAT CARDS (MINIMALIS) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Total Pengguna --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 card-animate">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                            Total Pengguna
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">
                            {{ number_format($jumlahUser, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Admin, mitra & pembeli terdaftar.
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        👥
                    </div>
                </div>
            </div>

            {{-- Total Mitra --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 card-animate delay-100">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                            Total Mitra
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">
                            {{ number_format($jumlahMitra, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Toko yang bergabung di Farmedia.
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        🏪
                    </div>
                </div>
            </div>

            {{-- Total Produk --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 card-animate delay-200">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                            Total Produk
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">
                            {{ number_format($jumlahProduk, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Produk aktif di marketplace.
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        🧺
                    </div>
                </div>
            </div>

            {{-- Konten Edukasi --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 card-animate delay-300">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                            Konten Edukasi
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700">
                            {{ number_format($jumlahEdukasi, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[11px] text-gray-400">
                            Artikel & video edukasi petani.
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        📚
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION BAWAH: GRAFIK + DETAIL ANGKA --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Grafik Distribusi (Satu Grafik Saja, Kecil) --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 card-animate">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Distribusi Data Sistem
                    </h3>
                    <span class="text-[11px] text-gray-400">
                        Visual ringkas
                    </span>
                </div>

                <div class="h-44">
                    <canvas id="summaryChart" class="w-full h-full"></canvas>
                </div>
            </div>

            {{-- Ringkasan Detail Angka --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 card-animate">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-800">
                        Ringkasan Detail
                    </h3>
                    <span class="text-[11px] text-gray-400">
                        Snapshot data utama
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Total Pengguna</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ number_format($jumlahUser, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[11px] text-emerald-700">
                            Akun terdaftar
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Total Mitra</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ number_format($jumlahMitra, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[11px] text-emerald-700">
                            Toko aktif
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Total Produk</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ number_format($jumlahProduk, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[11px] text-emerald-700">
                            Produk dijual
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Konten Edukasi</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ number_format($jumlahEdukasi, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-[11px] text-emerald-700">
                            Materi tersedia
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- CHART.JS (SATU GRAFIK SAJA, SIMPLE) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stats = {
                users: {{ $jumlahUser }},
                mitra: {{ $jumlahMitra }},
                produk: {{ $jumlahProduk }},
                edukasi: {{ $jumlahEdukasi }},
            };

            const canvas = document.getElementById('summaryChart');
            if (!canvas) return;

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: ['Pengguna', 'Mitra', 'Produk', 'Edukasi'],
                    datasets: [{
                        data: [stats.users, stats.mitra, stats.produk, stats.edukasi],
                        backgroundColor: 'rgba(16, 185, 129, 0.18)',
                        borderColor: '#059669',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10 },
                                color: '#6B7280'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(209, 213, 219, 0.35)' },
                            ticks: {
                                font: { size: 10 },
                                color: '#9CA3AF',
                                callback: function(value) {
                                    return value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    {{-- ANIMASI MASUK KARTU (HALUS & MINIMAL) --}}
    <style>
        .card-animate {
            opacity: 0;
            transform: translateY(4px);
            animation: fadeInUp 0.35s ease-out forwards;
        }
        .card-animate.delay-100 { animation-delay: .05s; }
        .card-animate.delay-200 { animation-delay: .1s;  }
        .card-animate.delay-300 { animation-delay: .15s; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>

</x-app-layout>
