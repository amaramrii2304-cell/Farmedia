{{-- resources/views/modulD/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tentang Sistem') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Farmedia: Sistem Aplikasi Mitra Pertanian Berbasis Marketplace dan Edukasi
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Versi 1.0 • Admin Panel
                    </p>
                </div>

                <div class="px-6 py-6 space-y-6">
                    {{-- Deskripsi utama --}}
                    <div class="space-y-3">
                        <p class="text-base leading-relaxed text-gray-700 dark:text-gray-200 text-justify">
                            <span class="font-semibold">Farmedia</span> adalah sebuah sistem aplikasi berbasis marketplace dan edukasi 
                            yang dirancang untuk membantu petani dalam mendapatkan berbagai kebutuhan pertanian secara mudah, cepat, 
                            dan terpercaya. Melalui Farmedia, petani dapat membeli alat pertanian, pupuk, pestisida, serta 
                            perlengkapan tani lainnya langsung dari mitra toko pertanian tanpa harus datang ke lokasi.
                        </p>

                        <p class="text-base leading-relaxed text-gray-700 dark:text-gray-200 text-justify">
                            Selain itu, Farmedia juga menyediakan fitur edukasi yang berisi panduan penggunaan alat dan pupuk, 
                            tips merawat tanaman, serta informasi pertanian modern yang relevan dengan kebutuhan petani masa kini. 
                            Dengan hadirnya Farmedia, diharapkan petani dapat lebih efisien dalam berbelanja, mendapatkan informasi 
                            yang bermanfaat, dan semakin siap menghadapi era pertanian digital.
                        </p>
                    </div>

                    {{-- Highlight fitur --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                Marketplace Pertanian
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Petani dapat memesan kebutuhan pertanian langsung dari mitra 
                                tanpa harus datang ke toko, menghemat waktu dan tenaga.
                            </p>
                        </div>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                Edukasi Interaktif
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Berisi panduan, tips, dan artikel edukatif seputar penggunaan 
                                alat, pupuk, serta praktik budidaya tanaman yang baik.
                            </p>
                        </div>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                Mendukung Petani Digital
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Membantu petani beradaptasi dengan perkembangan teknologi 
                                dan memanfaatkan informasi untuk meningkatkan produktivitas.
                            </p>
                        </div>
                    </div>

                    {{-- Info tambahan / tujuan sistem --}}
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Tujuan Pengembangan Farmedia
                        </h4>
                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-200 space-y-1">
                            <li>Mempermudah akses petani terhadap sarana dan prasarana pertanian.</li>
                            <li>Menyediakan informasi dan edukasi pertanian yang praktis dan aplikatif.</li>
                            <li>Membangun ekosistem kolaboratif antara petani dan mitra penyedia.</li>
                            <li>Mendukung transformasi digital di sektor pertanian.</li>
                        </ul>
                    </div>
                </div>

                {{-- Kontak di paling bawah --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                        Kontak Pengembang / Admin Sistem
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Jika Anda memiliki saran, masukan, atau menemukan kendala pada sistem Farmedia, 
                        silakan hubungi:
                    </p>

                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <p><span class="font-semibold">Email:</span> farmedia.support@example.com</p>
                        <p><span class="font-semibold">WhatsApp:</span> +62 859-6119-0817</p>
                        <p><span class="font-semibold">Alamat:</span> Politeknik Negeri Bengkalis, Bengkalis, Riau</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
