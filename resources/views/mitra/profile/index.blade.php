{{-- resources/views/mitra/profile/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-lg text-slate-900 leading-tight">
                    Pengaturan Toko
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Atur akun, pengiriman, pembayaran, dan mode libur toko Farmedia Anda.
                </p>
            </div>
        </div>
    </x-slot>

    @php
        /** @var \App\Models\User $user */
        $user = $user ?? auth()->user();
        $tab  = $tab ?? 'akun'; // akun / pengiriman / pembayaran / mode-libur
    @endphp

    <div class="py-8 bg-slate-50">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash pesan sukses --}}
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 flex items-start gap-3">
                    <div class="mt-0.5">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                            ✓
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">
                            Berhasil
                        </p>
                        <p class="text-xs text-emerald-700">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Error validasi --}}
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3">
                    <p class="text-sm font-semibold text-red-800 mb-1">
                        Terjadi kesalahan
                    </p>
                    <ul class="list-disc ms-5 text-[11px] text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                {{-- TAB NAV (Akun & Keamanan, Pengiriman, Pembayaran, Mode Libur) --}}
                <div class="border-b border-slate-200 bg-slate-50/60">
                    <nav class="flex flex-wrap gap-6 px-6 pt-4 text-xs font-medium text-slate-500">
                        <a href="{{ route('mitra.profile.edit', ['tab' => 'akun']) }}"
                           class="pb-3 border-b-2 {{ $tab === 'akun' ? 'border-emerald-500 text-emerald-700' : 'border-transparent hover:text-slate-800' }}">
                            Akun &amp; Keamanan
                        </a>
                        <a href="{{ route('mitra.profile.edit', ['tab' => 'pengiriman']) }}"
                           class="pb-3 border-b-2 {{ $tab === 'pengiriman' ? 'border-emerald-500 text-emerald-700' : 'border-transparent hover:text-slate-800' }}">
                            Pengiriman
                        </a>
                        <a href="{{ route('mitra.profile.edit', ['tab' => 'pembayaran']) }}"
                           class="pb-3 border-b-2 {{ $tab === 'pembayaran' ? 'border-emerald-500 text-emerald-700' : 'border-transparent hover:text-slate-800' }}">
                            Pembayaran
                        </a>
                        <a href="{{ route('mitra.profile.edit', ['tab' => 'mode-libur']) }}"
                           class="pb-3 border-b-2 {{ $tab === 'mode-libur' ? 'border-emerald-500 text-emerald-700' : 'border-transparent hover:text-slate-800' }}">
                            Mode Libur
                        </a>
                    </nav>
                </div>

                <div class="p-6 sm:p-7">
                    {{-- ========================= TAB: AKUN & KEAMANAN ========================== --}}
                    @if ($tab === 'akun')
                        <form action="{{ route('mitra.profile.update', ['tab' => 'akun']) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              class="space-y-6">
                            @csrf
                            <input type="hidden" name="section" value="akun">

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                {{-- Kiri: logo & identitas singkat --}}
                                <div class="lg:col-span-1">
                                    <div class="border border-slate-100 rounded-2xl bg-gradient-to-b from-slate-50 to-white p-5 flex flex-col items-center gap-5">
                                        @php
                                            $logoPath = $mitra->logo_path ?? null;
                                        @endphp

                                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full overflow-hidden border border-slate-200 shadow-sm flex items-center justify-center bg-white">
                                            @if ($logoPath)
                                                <img src="{{ asset('storage/' . $logoPath) }}"
                                                     alt="Logo Toko"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <div class="flex flex-col items-center justify-center text-center px-3">
                                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mb-1">
                                                        <span class="text-lg font-semibold text-slate-400">
                                                            {{ strtoupper(mb_substr($mitra->nama_toko, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-[11px] text-slate-400">
                                                        Belum ada logo toko
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="w-full space-y-2">
                                            <div class="text-center">
                                                <p class="text-sm font-semibold text-slate-900">
                                                    {{ $mitra->nama_toko }}
                                                </p>
                                                <p class="text-[11px] text-slate-500">
                                                    ID Mitra: #{{ $mitra->id }}
                                                </p>
                                            </div>

                                            <div class="w-full">
                                                <label for="upload-logo-toko" class="block text-[11px] font-medium text-slate-700 mb-1.5 text-left">
                                                    Logo / Foto Toko
                                                </label>
                                                <label class="flex flex-col items-center justify-center w-full px-3 py-2 border-2 border-dashed rounded-xl cursor-pointer border-emerald-100 bg-emerald-50/40 hover:bg-emerald-50 transition">
                                                    <span class="text-[11px] font-medium text-emerald-800">
                                                        Klik untuk pilih file
                                                    </span>
                                                    <span class="text-[10px] text-emerald-600">
                                                        JPG / PNG, maks. 2MB
                                                    </span>
                                                    <input
                                                        type="file"
                                                        id="upload-logo-toko"
                                                        name="logo"
                                                        accept="image/*"
                                                        class="hidden">
                                                </label>
                                                @error('logo')
                                                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Kanan: form akun & keamanan --}}
                                <div class="lg:col-span-2 space-y-6">

                                    {{-- Informasi Toko --}}
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/40 p-4 space-y-4">
                                        <div>
                                            <h4 class="text-xs font-semibold text-slate-800">
                                                Informasi Toko
                                            </h4>
                                            <p class="text-[11px] text-slate-500 mt-0.5">
                                                Data ini akan tampil di halaman toko dan detail produk.
                                            </p>
                                        </div>

                                        <div class="space-y-1.5">
                                            <label for="nama-toko-input" class="block text-[11px] font-medium text-slate-700">
                                                Nama Toko
                                            </label>
                                            <input
                                                type="text"
                                                id="nama-toko-input"
                                                name="nama_toko"
                                                value="{{ old('nama_toko', $mitra->nama_toko) }}"
                                                class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                                required>
                                        </div>

                                        <div class="space-y-1.5">
                                            <label for="alamat-toko-input" class="block text-[11px] font-medium text-slate-700">
                                                Alamat Toko
                                            </label>
                                            <textarea
                                                id="alamat-toko-input"
                                                name="alamat"
                                                rows="3"
                                                class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                                required>{{ old('alamat', $mitra->alamat) }}</textarea>
                                        </div>

                                        <div class="space-y-1.5">
                                            <label for="telepon-toko-input" class="block text-[11px] font-medium text-slate-700">
                                                Nomor Telepon / WhatsApp
                                            </label>
                                            <input
                                                type="text"
                                                id="telepon-toko-input"
                                                name="phone"
                                                value="{{ old('phone', $mitra->phone) }}"
                                                class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                                placeholder="Contoh: 0812xxxxxxx">
                                        </div>
                                    </div>

                                    {{-- Informasi Akun & Keamanan --}}
                                    <div class="rounded-2xl border border-slate-100 bg-white p-4 space-y-4">
                                        <div>
                                            <h4 class="text-xs font-semibold text-slate-800">
                                                Akun &amp; Keamanan
                                            </h4>
                                            <p class="text-[11px] text-slate-500 mt-0.5">
                                                Atur email login, password, dan perlindungan akun.
                                            </p>
                                        </div>

                                        {{-- Email (readonly) --}}
                                        <div class="space-y-1.5">
                                            <label for="email-akun-input" class="block text-[11px] font-medium text-slate-700">
                                                Email Akun
                                            </label>
                                            <input
                                                type="email"
                                                id="email-akun-input"
                                                value="{{ $user->email }}"
                                                class="w-full border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-600 cursor-not-allowed shadow-sm"
                                                readonly>
                                            <p class="text-[11px] text-slate-500 mt-1">
                                                Untuk mengubah email, gunakan menu Profil Pengguna (bawaan Laravel).
                                            </p>
                                        </div>

                                        {{-- Password Baru --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-1.5">
                                                <label for="password-baru-input" class="block text-[11px] font-medium text-slate-700">
                                                    Password Baru
                                                </label>
                                                <input
                                                    type="password"
                                                    id="password-baru-input"
                                                    name="password"
                                                    class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                                    autocomplete="new-password">
                                                <p class="text-[11px] text-slate-500 mt-1">
                                                    Kosongkan jika tidak ingin mengubah password.
                                                </p>
                                            </div>
                                            <div class="space-y-1.5">
                                                <label for="konfirmasi-password-input" class="block text-[11px] font-medium text-slate-700">
                                                    Konfirmasi Password Baru
                                                </label>
                                                <input
                                                    type="password"
                                                    id="konfirmasi-password-input"
                                                    name="password_confirmation"
                                                    class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                                    autocomplete="new-password">
                                            </div>
                                        </div>

                                        {{-- Verifikasi Akun via SMS (switch sederhana) --}}
                                        <div class="pt-3 border-t border-dashed border-slate-200 flex items-center justify-between">
                                            <div>
                                                <p class="text-[11px] font-semibold text-slate-800">
                                                    Verifikasi Akun via SMS
                                                </p>
                                                <p class="text-[11px] text-slate-500">
                                                    Aktifkan untuk perlindungan tambahan saat ada aktivitas berisiko.
                                                </p>
                                            </div>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="sms_verification_enabled" value="1" aria-label="Aktifkan Verifikasi SMS" class="sr-only peer"
                                                       {{ $mitra->sms_verification_enabled ? 'checked' : '' }}>
                                                <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition relative">
                                                    <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transform peer-checked:translate-x-4 transition"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- Tombol simpan --}}
                            <div class="pt-3 border-t border-dashed border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                                <a href="{{ route('mitra.dashboard') }}"
                                   class="inline-flex items-center justify-center px-5 py-2.5 border border-slate-200 text-sm font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50">
                                    Batal
                                </a>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 shadow-sm">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- ========================= TAB: PENGIRIMAN ========================== --}}
                    @if ($tab === 'pengiriman')
                        <form action="{{ route('mitra.profile.update', ['tab' => 'pengiriman']) }}"
                              method="POST"
                              class="space-y-6">
                            @csrf
                            <input type="hidden" name="section" value="pengiriman">

                            {{-- Pengaturan Alamat --}}
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 space-y-3">
                                <h4 class="text-xs font-semibold text-slate-800">
                                    <label for="pickup-address-input">Pengaturan Alamat</label>
                                </h4>
                                <p class="text-[11px] text-slate-500">
                                    Alamat ini digunakan sebagai titik pengiriman / pickup pesanan.
                                </p>

                                <textarea
                                    id="pickup-address-input"
                                    name="pickup_address"
                                    rows="3"
                                    class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                    required>{{ old('pickup_address', $mitra->pickup_address ?? $mitra->alamat) }}</textarea>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 shadow-sm">
                                    Simpan Pengaturan Pengiriman
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- ========================= TAB: PEMBAYARAN ========================== --}}
                    @if ($tab === 'pembayaran')
                        <form action="{{ route('mitra.profile.update', ['tab' => 'pembayaran']) }}"
                              method="POST"
                              class="space-y-5">
                            @csrf
                            <input type="hidden" name="section" value="pembayaran">

                            <div class="rounded-2xl border border-slate-100 bg-white">
                                <div class="px-4 pt-4 pb-3 border-b border-slate-100">
                                    <h4 class="text-xs font-semibold text-slate-800">
                                        Rekening Pencairan
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Data ini digunakan untuk pencairan hasil penjualan dari Farmedia ke rekening Anda.
                                    </p>
                                </div>

                                <div class="px-4 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div class="space-y-1.5">
                                        <label for="bank-name-input" class="block text-[11px] font-medium text-slate-700">
                                            Nama Bank
                                        </label>
                                        <input type="text" name="bank_name"
                                               id="bank-name-input"
                                               value="{{ old('bank_name', $mitra->bank_name) }}"
                                               class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                               placeholder="BCA / BRI / Mandiri">
                                    </div>

                                    <div class="space-y-1.5">
                                        <label for="account-number-input" class="block text-[11px] font-medium text-slate-700">
                                            Nomor Rekening
                                        </label>
                                        <input type="text" name="account_number"
                                               id="account-number-input"
                                               value="{{ old('account_number', $mitra->bank_account_number) }}"
                                               class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>

                                    <div class="space-y-1.5">
                                        <label for="account-name-input" class="block text-[11px] font-medium text-slate-700">
                                            Nama Pemilik Rekening
                                        </label>
                                        <input type="text" name="account_name"
                                               id="account-name-input"
                                               value="{{ old('account_name', $mitra->bank_account_name ?? $user->name) }}"
                                               class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 shadow-sm">
                                    Simpan Pengaturan Pembayaran
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- ========================= TAB: MODE LIBUR ========================== --}}
                    @if ($tab === 'mode-libur')
                        <form action="{{ route('mitra.profile.update', ['tab' => 'mode-libur']) }}"
                              method="POST"
                              class="space-y-5">
                            @csrf
                            <input type="hidden" name="section" value="mode_libur">

                            <div class="rounded-2xl border border-slate-100 bg-white">
                                <div class="px-4 pt-4 pb-3 border-b border-slate-100 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-800">
                                            Fitur Toko Libur
                                        </h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Saat mode libur aktif, pembeli tidak dapat membuat pesanan baru.
                                            Pesanan yang sudah ada tetap harus diselesaikan.
                                        </p>
                                    </div>

                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="vacation_mode" value="1" aria-label="Aktifkan Mode Libur Toko" class="sr-only peer"
                                               {{ $mitra->vacation_mode ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition relative">
                                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transform peer-checked:translate-x-4 transition"></div>
                                        </div>
                                    </label>
                                </div>

                                <div class="px-4 py-4 space-y-2">
                                    <label for="vacation-message-input" class="block text-[11px] font-medium text-slate-700">
                                        Pesan Otomatis Saat Toko Libur
                                    </label>
                                    <textarea
                                        id="vacation-message-input"
                                        name="vacation_message"
                                        rows="3"
                                        class="w-full border-slate-200 rounded-xl text-sm shadow-sm focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="Contoh: Toko sedang libur sampai tanggal 10, pesanan akan diproses setelah tanggal tersebut.">{{ old('vacation_message', $mitra->vacation_message) }}</textarea>
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 shadow-sm">
                                    Simpan Pengaturan Mode Libur
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>