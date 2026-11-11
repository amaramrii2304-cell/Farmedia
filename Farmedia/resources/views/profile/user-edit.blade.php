{{-- resources/views/profile/user-edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profil Saya
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
                {{-- Judul + deskripsi ala Shopee --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Profil Saya
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Kelola informasi profil Anda untuk mengontrol, melindungi, dan mengamankan akun.
                    </p>
                </div>

                {{-- FORM PROFIL (pakai partial yang kamu kirim barusan) --}}
                <div class="px-6 py-6">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- garis tipis pemisah --}}
                <hr class="border-gray-200">

                {{-- BAGIAN UBAH PASSWORD --}}
                <div class="px-6 py-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">
                        Ubah Password
                    </h3>
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
