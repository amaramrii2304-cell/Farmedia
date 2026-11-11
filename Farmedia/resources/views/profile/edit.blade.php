<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Profil Akun
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ===================== BANNER RINGKASAN USER ===================== --}}
            <div class="bg-gradient-to-r from-emerald-600 to-green-500 rounded-2xl shadow-lg px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    {{-- Avatar inisial --}}
                    <div class="w-14 h-14 rounded-full bg-emerald-700/60 flex items-center justify-center text-white text-2xl font-semibold shadow-md">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs text-emerald-50/80">Selamat datang,</p>
                        <h1 class="text-lg sm:text-xl font-semibold text-white leading-tight">
                            {{ auth()->user()->name }}
                        </h1>
                        <p class="text-xs text-emerald-50/90 mt-1">
                            {{ auth()->user()->email }}
                        </p>
                        @if(!empty(auth()->user()->role))
                            <p class="inline-flex items-center gap-1 mt-1 text-[11px] px-2 py-0.5 rounded-full bg-emerald-900/50 text-emerald-50 border border-emerald-300/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                                {{ strtoupper(auth()->user()->role) }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="text-xs text-emerald-50/85 text-left sm:text-right">
                    <p>Bergabung sejak</p>
                    <p class="font-semibold">
                        {{ auth()->user()->created_at?->format('d M Y') }}
                    </p>
                </div>
            </div>

            {{-- ===================== KARTU-KARTU PENGATURAN PROFIL ===================== --}}
            <div class="space-y-6">

                {{-- INFORMASI PROFIL --}}
                <section class="bg-white dark:bg-gray-800 shadow-md rounded-2xl border border-emerald-50 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-emerald-100 dark:border-gray-700 bg-emerald-50/70 dark:bg-gray-900/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white shadow">
                            {{-- icon user --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0116 0" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">
                                Informasi Profil
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                Ubah nama, alamat email, dan informasi dasar akun kamu.
                            </p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </section>

                {{-- UBAH PASSWORD --}}
                <section class="bg-white dark:bg-gray-800 shadow-md rounded-2xl border border-emerald-50 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/70 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-500/90 flex items-center justify-center text-white shadow">
                            {{-- icon lock --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0v4" />
                                <rect x="5" y="11" width="14" height="10" rx="2" ry="2" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                Keamanan & Password
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                Ganti kata sandi secara berkala untuk menjaga keamanan akun.
                            </p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </section>

                {{-- HAPUS AKUN --}}
                <section class="bg-white dark:bg-gray-800 shadow-md rounded-2xl border border-red-100 dark:border-red-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-red-100 dark:border-red-700 bg-red-50/80 dark:bg-red-900/40 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-red-600 flex items-center justify-center text-white shadow">
                            {{-- icon warning --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 4.3L3.4 17a1 1 0 00.9 1.5h15.4a1 1 0 00.9-1.5L13.7 4.3a1 1 0 00-1.7 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-red-700 dark:text-red-200">
                                Hapus Akun
                            </h3>
                            <p class="text-xs text-red-600/90 dark:text-red-200/90">
                                Tindakan ini bersifat permanen. Data dan riwayat akan dihapus.
                            </p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </section>

            </div>

        </div>
    </div>
</x-app-layout>
