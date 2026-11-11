<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            Tambah Pengguna
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

        {{-- BAR ATAS --}}
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.pengguna.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-emerald-200 bg-white text-emerald-800 text-sm hover:bg-emerald-50 transition">
                <span class="text-lg">←</span>
                <span>Kembali</span>
            </a>

            <h1 class="text-lg sm:text-2xl font-bold text-emerald-900">
                Tambah Pengguna
            </h1>

            <div class="hidden sm:block w-16"></div>
        </div>

        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-3">
                <span class="mt-0.5 text-lg">✔</span>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- ERROR VALIDATION --}}
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- CARD FORM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-6 sm:p-8">

            <div class="mb-6">
                <p class="text-xs uppercase tracking-[0.2em] text-emerald-600">
                    Form Pengguna Baru
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    Isi data di bawah untuk membuat akun admin, mitra, atau pembeli baru di Farmedia.
                </p>
            </div>

            <form action="{{ route('admin.pengguna.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- NAMA & EMAIL --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- PASSWORD & ROLE --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Password
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
                        @error('password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Role / Hak Akses
                        </label>
                        <select
                            id="role"
                            name="role"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
                            <option value="admin"   {{ old('role') == 'admin'   ? 'selected' : '' }}>Admin</option>
                            <option value="mitra"   {{ old('role') == 'mitra'   ? 'selected' : '' }}>Penjual / Mitra</option>
                            <option value="pembeli" {{ old('role') == 'pembeli' ? 'selected' : '' }}>Pembeli</option>
                        </select>
                        @error('role')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-gray-500 mt-1">
                            Role akan menentukan tampilan menu dan fitur yang dapat diakses.
                        </p>
                    </div>
                </div>

                {{-- FOOTER BUTTON --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.pengguna.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                                   bg-gradient-to-r from-emerald-500 to-emerald-600 shadow-md
                                   hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg transition">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
