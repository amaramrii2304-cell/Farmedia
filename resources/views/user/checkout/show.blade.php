<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Checkout
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- SATU CARD BESAR --}}
            <form action="{{ route('checkout.store', $product->id) }}" method="POST"
                  class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl overflow-hidden">
                @csrf

                {{-- HEADER CARD --}}
                <div class="bg-gradient-to-r from-emerald-600 to-green-500 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="text-sm text-emerald-50/80">Langkah 2 dari 2</p>
                        <h3 class="text-xl font-semibold text-white">Konfirmasi Pesanan</h3>
                        <p class="text-xs text-emerald-50/80 mt-1">
                            Pastikan alamat, pengiriman, dan pembayaran sudah sesuai.
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-emerald-50/80">Total estimasi</p>
                        <p id="headerTotal" class="text-lg font-bold text-white">
                            {{-- akan diisi JS --}}
                            Rp {{ number_format($product->harga + 10000, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-6">

                    {{-- ===================== ALAMAT PENGIRIMAN ===================== --}}
                    <section class="border border-emerald-50/60 dark:border-gray-700 rounded-xl p-4 sm:p-5 bg-emerald-50/40 dark:bg-gray-900/60">
                        <div class="flex items-start gap-3">
                            <div class="mt-1">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-600 text-white text-sm">
                                    1
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <h3 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                        Alamat Pengiriman
                                    </h3>
                                    <button
                                        type="button"
                                        onclick="document.getElementById('modalAlamat').classList.remove('hidden')"
                                        class="text-xs text-emerald-700 hover:text-emerald-900 dark:text-emerald-300 underline">
                                        Ubah Alamat
                                    </button>
                                </div>

                                <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                    {{ $user->name }}
                                </p>
                                <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 leading-relaxed">
                                    {{ $user->alamat_lengkap ?? 'Alamat belum diatur' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- ===================== PRODUK ===================== --}}
                    <section class="border border-gray-100 dark:border-gray-700 rounded-xl p-4 sm:p-5 bg-white dark:bg-gray-900/70">
                        <div class="flex items-start gap-4">
                            <div class="shrink-0">
                                <img src="{{ asset('storage/' . $product->foto_produk) }}"
                                     class="w-24 h-24 object-contain rounded-xl bg-gray-100 dark:bg-gray-800">
                            </div>
                            <div class="flex-1 space-y-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $product->nama_produk }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Toko: {{ $product->mitra->nama_toko ?? '-' }}
                                </p>
                                <p class="text-emerald-600 dark:text-emerald-400 font-bold text-lg mt-2">
                                    Rp {{ number_format($product->harga, 0, ',', '.') }}
                                </p>

                                <div class="mt-3">
                                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Jumlah</label>
                                    <input type="number" id="jumlahInput" value="1" min="1" max="{{ $product->stok }}"
                                           class="mt-1 w-24 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ===================== PENGIRIMAN & PEMBAYARAN ===================== --}}
                    <section class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

                        {{-- PENGIRIMAN --}}
                        <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-4 sm:p-5 bg-white dark:bg-gray-900/70">
                            <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-100 mb-3">
                                2. Pengiriman
                            </h3>

                            <div class="space-y-4 text-sm">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-300">
                                        Pilih Kurir
                                    </label>
                                    <select id="kurirSelect"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="10000">Reguler (Rp 10.000)</option>
                                        <option value="20000">Express (Rp 20.000)</option>
                                        <option value="7000">Hemat (Rp 7.000)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-300">
                                        Metode Pengiriman
                                    </label>
                                    <select id="pengirimanSelect"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="delivery">Dikirim ke Alamat</option>
                                        <option value="pickup">Ambil di Toko</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- PEMBAYARAN --}}
                        <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-4 sm:p-5 bg-white dark:bg-gray-900/70">
                            <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-100 mb-3">
                                3. Metode Pembayaran
                            </h3>

                            <select id="paymentSelect"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm">

                                {{-- Delivery Only --}}
                                <option value="cod">Bayar di Tempat (COD)</option>

                                {{-- Delivery + Pickup --}}
                                <option value="transfer">Transfer Bank</option>

                                {{-- Pickup Only --}}
                                <option value="cash" class="pickup-only">Bayar di Toko (Cash)</option>
                            </select>

                            <p class="mt-3 text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                Untuk <span class="font-semibold">Ambil di Toko</span>, kamu bisa memilih
                                <span class="font-semibold">Cash</span>Transfer.  
                                Untuk <span class="font-semibold">Dikirim</span>, kamu bisa memilih COD atau Transfer.
                            </p>
                        </div>

                    </section>

                    {{-- ===================== RINCIAN PEMBAYARAN ===================== --}}
                    <section class="border border-gray-100 dark:border-gray-700 rounded-xl p-4 sm:p-5 bg-white dark:bg-gray-900/70 space-y-2 text-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">
                            Rincian Pembayaran
                        </h3>

                        <p>Harga Barang:
                            <span class="float-right font-semibold" id="hargaBarang">
                                Rp {{ number_format($product->harga, 0, ',', '.') }}
                            </span>
                        </p>

                        <p>Ongkos Kirim:
                            <span class="float-right font-semibold" id="ongkirLabel">
                                Rp 10.000
                            </span>
                        </p>

                        <hr class="my-2">

                        <p class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                            Total:
                            <span class="float-right" id="totalHarga">
                                Rp {{ number_format($product->harga + 10000, 0, ',', '.') }}
                            </span>
                        </p>

                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            Metode Pembayaran: <span class="font-semibold" id="paymentLabel">Bayar di Tempat (COD)</span>
                        </p>
                    </section>

                    {{-- HIDDEN INPUT UNTUK BACKEND --}}
                    <input type="hidden" name="jumlah" id="jumlahHidden" value="1">
                    <input type="hidden" name="ongkir" id="ongkirHidden" value="10000">
                    <input type="hidden" name="metode_pembayaran" id="paymentHidden" value="cod">
                    <input type="hidden" name="metode_pengiriman" id="pengirimanHidden" value="delivery">
                    <input type="hidden" name="alamat_pengiriman" value="{{ $user->alamat_lengkap }}">

                </div>

                {{-- FOOTER CARD --}}
                <div class="border-t border-gray-100 dark:border-gray-700 px-6 py-4 bg-gray-50/80 dark:bg-gray-900/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Dengan menekan tombol <span class="font-semibold">Buat Pesanan</span>, kamu menyetujui
                        ketentuan transaksi di Farmedia.
                    </div>
                    <button type="submit"
                            class="inline-flex justify-center px-6 py-2.5 rounded-full font-semibold text-sm
                                   bg-emerald-600 hover:bg-emerald-700 text-white shadow-md">
                        Buat Pesanan
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ===================== MODAL UBAH ALAMAT ===================== --}}
    <div id="modalAlamat"
         class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">

        <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-xl animate-fadeIn">
            <h2 class="text-lg font-semibold mb-4">Ubah Alamat Pengiriman</h2>

            <form action="{{ route('user.updateAddress') }}" method="POST">
                @csrf

                <textarea
                    name="alamat_lengkap"
                    class="w-full border rounded-lg p-3 text-sm"
                    rows="4"
                    placeholder="Masukkan alamat lengkap...">{{ $user->alamat_lengkap }}</textarea>

                <div class="flex justify-end gap-3 mt-4">
                    <button type="button"
                            onclick="document.getElementById('modalAlamat').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 rounded-lg">
                        Batal
                    </button>

                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    </style>

    <script>
        function updateAll() {
            const jumlahInput      = document.getElementById('jumlahInput');
            const kurirSelect      = document.getElementById('kurirSelect');
            const pengirimanSelect = document.getElementById('pengirimanSelect');
            const paymentSelect    = document.getElementById('paymentSelect');

            const jumlah      = parseInt(jumlahInput.value) || 1;
            const hargaSatuan = {{ $product->harga }};
            const isPickup    = pengirimanSelect.value === "pickup";

            const ongkir = isPickup ? 0 : parseInt(kurirSelect.value);

            const barangTotal = jumlah * hargaSatuan;
            const total       = barangTotal + ongkir;

            document.getElementById('hargaBarang').innerText =
                "Rp " + barangTotal.toLocaleString("id-ID");

            document.getElementById('ongkirLabel').innerText =
                "Rp " + ongkir.toLocaleString("id-ID");

            document.getElementById('totalHarga').innerText =
                "Rp " + total.toLocaleString("id-ID");

            const headerTotal = document.getElementById('headerTotal');
            if (headerTotal) {
                headerTotal.innerText = "Rp " + total.toLocaleString("id-ID");
            }

            document.getElementById('jumlahHidden').value       = jumlah;
            document.getElementById('ongkirHidden').value       = ongkir;
            document.getElementById('pengirimanHidden').value   = pengirimanSelect.value;
            document.getElementById('paymentHidden').value      = paymentSelect.value;

            const paymentLabel = document.getElementById('paymentLabel');
            if (paymentLabel) {
                const text = paymentSelect.options[paymentSelect.selectedIndex].text;
                paymentLabel.innerText = text;
            }

            kurirSelect.disabled = isPickup;
        }

        document.getElementById('jumlahInput').addEventListener('input', updateAll);
        document.getElementById('kurirSelect').addEventListener('change', updateAll);
        document.getElementById('pengirimanSelect').addEventListener('change', updateAll);
        document.getElementById('paymentSelect').addEventListener('change', updateAll);

        updateAll();
    </script>

</x-app-layout>
