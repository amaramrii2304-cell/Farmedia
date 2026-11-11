<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            Edit Pengguna
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
                Edit Pengguna
            </h1>

            <div class="hidden sm:block w-16"></div>
        </div>

        {{-- FLASH & ERROR --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-3">
                <span class="mt-0.5 text-lg">✔</span>
                <p>{{ session('success') }}</p>
            </div>
        @endif

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

            {{-- HEADER KECIL DI DALAM CARD --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-semibold">
                    {{ strtoupper(mb_substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-600">
                        Data Akun
                    </p>
                    <p class="text-sm text-gray-500">
                        Perbarui informasi profil & akses role pengguna.
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.pengguna.update', $user->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- NAMA & EMAIL --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
                    </div>
                </div>

                {{-- ROLE --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Role / Hak Akses
                        </label>
                        <select
                            name="role"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
                            <option value="admin"   {{ $user->role === 'admin'   ? 'selected' : '' }}>Admin</option>
                            <option value="mitra"   {{ $user->role === 'mitra'   ? 'selected' : '' }}>Penjual / Mitra</option>
                            <option value="pembeli" {{ $user->role === 'pembeli' ? 'selected' : '' }}>Pembeli</option>
                        </select>
                        <p class="mt-1 text-[11px] text-gray-500">
                            Ubah dengan hati-hati. Role menentukan akses menu di Farmedia.
                        </p>
                    </div>

                    {{-- PASSWORD BARU (OPSIONAL) --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 tracking-wide mb-1">
                            Password Baru
                            <span class="text-[11px] font-normal text-gray-400">(kosongkan jika tidak diubah)</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                        >
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
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
