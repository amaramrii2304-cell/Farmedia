<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            Daftar Pengguna
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        {{-- BAR ATAS: KEMBALI + INFO + TAMBAH --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-emerald-200 bg-white text-emerald-800 text-sm hover:bg-emerald-50 transition">
                    <span class="text-lg">←</span>
                    <span>Kembali ke Dashboard</span>
                </a>

                <div class="hidden sm:block">
                    <p class="text-xs text-emerald-700 uppercase tracking-[0.2em]">
                        Pengelolaan Akun
                    </p>
                    <p class="text-sm text-gray-500">
                        Total pengguna terdaftar: <span class="font-semibold text-emerald-700">{{ $users->count() }}</span>
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.pengguna.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                      bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-sm font-semibold
                      shadow-md hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg transition">
                <span class="text-lg">＋</span>
                <span>Tambah Pengguna</span>
            </a>
        </div>

        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-3">
                <span class="mt-0.5 text-lg">✔</span>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        {{-- CARD TABEL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 overflow-hidden">

            {{-- header kecil di dalam card --}}
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-emerald-900">Daftar Pengguna</h3>
                    <p class="text-xs text-gray-500">
                        Admin, mitra, dan pembeli yang terdaftar di Farmedia.
                    </p>
                </div>

                {{-- placeholder search (boleh dipakai nanti kalau logika sudah ada) --}}
                <div class="w-full sm:w-64">
                    <div class="relative">
                        <input
                            type="text"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-9 py-2 text-xs
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500"
                            placeholder="Cari nama / email (belum aktif)"
                            disabled
                        >
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm">🔍</span>
                    </div>
                </div>
            </div>

            {{-- TABLE WRAPPER --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-emerald-50 text-left text-[11px] font-semibold text-emerald-800 uppercase tracking-wide">
                            <th class="px-4 sm:px-6 py-3">Nama</th>
                            <th class="px-4 sm:px-6 py-3">Email</th>
                            <th class="px-4 sm:px-6 py-3">Role</th>
                            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Tanggal Bergabung</th>
                            <th class="px-4 sm:px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-emerald-50/40 transition-colors">
                                {{-- NAMA --}}
                                <td class="px-4 sm:px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-semibold">
                                            {{ strtoupper(mb_substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">
                                                {{ $user->name }}
                                            </p>
                                            <p class="text-[11px] text-gray-500">
                                                ID: {{ $user->id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- EMAIL --}}
                                <td class="px-4 sm:px-6 py-3">
                                    <p class="text-sm text-gray-700">{{ $user->email }}</p>
                                </td>

                                {{-- ROLE --}}
                                <td class="px-4 sm:px-6 py-3">
                                    @php
                                        $roleColor = match($user->role) {
                                            'admin' => 'bg-red-100 text-red-700',
                                            'mitra' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-emerald-100 text-emerald-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold {{ $roleColor }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>

                                {{-- TANGGAL --}}
                                <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                    <p class="text-xs text-gray-600">
                                        {{ $user->created_at->format('d M Y') }}
                                    </p>
                                </td>

                                {{-- AKSI --}}
                                <td class="px-4 sm:px-6 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- EDIT --}}
                                        <a href="{{ route('admin.pengguna.edit', $user->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-xl border border-emerald-200 bg-white text-[11px] font-medium text-emerald-800 hover:bg-emerald-50 transition">
                                            ✏️ <span class="ml-1 hidden sm:inline">Edit</span>
                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('admin.pengguna.destroy', $user->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-xl border border-red-200 bg-white text-[11px] font-medium text-red-600 hover:bg-red-50 transition">
                                                🗑 <span class="ml-1 hidden sm:inline">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 sm:px-6 py-6 text-center text-sm text-gray-500">
                                    Belum ada pengguna yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
