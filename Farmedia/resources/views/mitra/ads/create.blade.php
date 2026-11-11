{{-- resources/views/mitra/ads/create.blade.php --}}
<x-app-layout>
    <div class="space-y-6">
        {{-- Flash --}}
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-emerald-900">Buat Iklan / Promosi</h2>
                <p class="text-sm text-emerald-900/70">
                    Pilih produk, placement, paket durasi — lalu submit pengajuan.
                </p>
            </div>

            <a href="{{ route('mitra.ads.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
                ← Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Form --}}
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm lg:col-span-2">
                <form method="POST" action="{{ route('mitra.ads.store') }}" class="space-y-5">
                    @csrf

                    {{-- Produk --}}
                    <div>
                        <label class="block text-xs font-semibold text-emerald-800">
                            Pilih Produk <span class="text-rose-600">*</span>
                        </label>
                        <select name="product_id" id="product_id"
                                class="mt-2 block w-full rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">— Pilih produk —</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)
                                        data-nama="{{ $p->nama_produk }}"
                                        data-kategori="{{ $p->kategori_produk }}"
                                        data-harga="{{ $p->harga }}"
                                        data-stok="{{ $p->stok }}">
                                    {{ $p->nama_produk }} — {{ $p->kategori_produk ?? 'Tanpa kategori' }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Placement --}}
                    <div>
                        <p class="block text-xs font-semibold text-emerald-800">
                            Placement <span class="text-rose-600">*</span>
                        </p>

                        @php $oldPlacement = old('placement', 'home'); @endphp

                        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4 hover:bg-emerald-50">
                                <input type="radio" name="placement" value="home" class="mt-1"
                                       @checked($oldPlacement === 'home')>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-900">Home</p>
                                    <p class="text-xs text-emerald-900/60">Ditampilkan di area promosi beranda.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4 hover:bg-emerald-50">
                                <input type="radio" name="placement" value="category" class="mt-1"
                                       @checked($oldPlacement === 'category')>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-900">Category</p>
                                    <p class="text-xs text-emerald-900/60">Muncul saat user filter kategori tertentu.</p>
                                </div>
                            </label>
                        </div>

                        @error('placement')
                            <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Target Kategori (muncul kalau placement=category) --}}
                    <div id="targetKategoriWrap" class="hidden">
                        <label class="block text-xs font-semibold text-emerald-800">
                            Target Kategori <span class="text-rose-600">*</span>
                        </label>

                        <select name="target_kategori" id="target_kategori"
                                class="mt-2 block w-full rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">— Pilih kategori —</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" @selected(old('target_kategori') == $kat)>{{ $kat }}</option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs text-emerald-900/60">
                            Wajib diisi jika placement <b>Category</b>.
                        </p>

                        @error('target_kategori')
                            <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Paket Durasi --}}
                    <div>
                        <p class="block text-xs font-semibold text-emerald-800">
                            Paket Durasi <span class="text-rose-600">*</span>
                        </p>
                        <p class="mt-1 text-xs text-emerald-900/60">
                            Harga otomatis mengikuti placement & durasi yang dipilih.
                        </p>

                        <div id="packageGrid" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            {{-- diisi via JS --}}
                        </div>

                        <input type="hidden" name="duration_days" id="duration_days" value="{{ old('duration_days', '') }}">

                        @error('duration_days')
                            <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-emerald-900/60">
                            Setelah submit, lakukan transfer sesuai tagihan lalu upload bukti transfer di detail iklan.
                        </p>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                            Submit Pengajuan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Preview & Tagihan --}}
            <div class="space-y-4">
                {{-- Preview card --}}
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-emerald-900">Preview Ringkas</p>
                    <p class="mt-1 text-xs text-emerald-900/60">Biar yakin sebelum submit.</p>

                    <div class="mt-4 space-y-3 rounded-2xl bg-emerald-50/50 p-4">
                        <div>
                            <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">PRODUK</p>
                            <p id="pvNama" class="mt-1 text-sm font-bold text-emerald-900">—</p>
                            <p id="pvKategori" class="mt-1 text-xs text-emerald-900/60">Kategori: —</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-white p-3 border border-emerald-100">
                                <p class="text-[11px] font-semibold text-emerald-700">Harga Produk</p>
                                <p id="pvHargaProduk" class="mt-1 text-sm font-bold text-emerald-900">—</p>
                            </div>
                            <div class="rounded-xl bg-white p-3 border border-emerald-100">
                                <p class="text-[11px] font-semibold text-emerald-700">Stok</p>
                                <p id="pvStok" class="mt-1 text-sm font-bold text-emerald-900">—</p>
                            </div>
                        </div>

                        <div class="rounded-xl bg-white p-3 border border-emerald-100">
                            <p class="text-[11px] font-semibold text-emerald-700">Placement</p>
                            <p id="pvPlacement" class="mt-1 text-sm font-bold text-emerald-900">—</p>
                            <p id="pvTarget" class="mt-1 text-xs text-emerald-900/60">Target: —</p>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan Tagihan --}}
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-emerald-900">Ringkasan Tagihan</p>
                    <p class="mt-1 text-xs text-emerald-900/60">Total yang perlu ditransfer.</p>

                    <div class="mt-4 space-y-3 rounded-2xl bg-emerald-50/50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-emerald-900/70">Paket</p>
                            <p id="billPaket" class="text-sm font-semibold text-emerald-900">—</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-emerald-900/70">Durasi</p>
                            <p id="billDurasi" class="text-sm font-semibold text-emerald-900">—</p>
                        </div>
                        <div class="h-px bg-emerald-200/70"></div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-emerald-900/70">Total</p>
                            <p id="billTotal" class="text-lg font-bold text-emerald-900">—</p>
                        </div>

                        <div class="rounded-xl bg-white border border-emerald-100 p-3">
                            <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">TRANSFER KE</p>
                            <p class="mt-1 text-sm font-bold text-emerald-900">{{ $bank['name'] ?? '-' }}</p>
                            <p class="text-xs text-emerald-900/70">a.n. {{ $bank['account_name'] ?? '-' }}</p>
                            <p class="mt-1 text-sm font-semibold text-emerald-900">{{ $bank['account_number'] ?? '-' }}</p>
                            <p class="mt-2 text-xs text-emerald-900/60">
                                Setelah submit, transfer sesuai total lalu upload bukti.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JS: toggle + preview + paket + tagihan --}}
    <script>
        (function () {
            const PACKAGES = @json($packages ?? []);

            const productSelect = document.getElementById('product_id');

            const pvNama = document.getElementById('pvNama');
            const pvKategori = document.getElementById('pvKategori');
            const pvHargaProduk = document.getElementById('pvHargaProduk');
            const pvStok = document.getElementById('pvStok');
            const pvPlacement = document.getElementById('pvPlacement');
            const pvTarget = document.getElementById('pvTarget');

            const targetWrap = document.getElementById('targetKategoriWrap');
            const targetSelect = document.getElementById('target_kategori');

            const packageGrid = document.getElementById('packageGrid');
            const durationInput = document.getElementById('duration_days');

            const billPaket = document.getElementById('billPaket');
            const billDurasi = document.getElementById('billDurasi');
            const billTotal = document.getElementById('billTotal');

            function formatRupiah(val) {
                if (val === null || val === undefined || val === '') return '—';
                const n = Number(val);
                if (Number.isNaN(n)) return '—';
                return 'Rp ' + n.toLocaleString('id-ID');
            }

            function getPlacement() {
                const el = document.querySelector('input[name="placement"]:checked');
                return el ? el.value : 'home';
            }

            function updateTargetVisibility() {
                const placement = getPlacement();
                if (placement === 'category') targetWrap.classList.remove('hidden');
                else targetWrap.classList.add('hidden');
            }

            function updatePreviewProduct() {
                const opt = productSelect.options[productSelect.selectedIndex];
                if (!opt || !opt.value) {
                    pvNama.textContent = '—';
                    pvKategori.textContent = 'Kategori: —';
                    pvHargaProduk.textContent = '—';
                    pvStok.textContent = '—';
                    return;
                }

                pvNama.textContent = opt.dataset.nama || '—';
                pvKategori.textContent = 'Kategori: ' + (opt.dataset.kategori || '—');
                pvHargaProduk.textContent = formatRupiah(opt.dataset.harga);
                pvStok.textContent = (opt.dataset.stok ?? '—');
            }

            function updatePreviewPlacement() {
                const placement = getPlacement();
                pvPlacement.textContent = placement ? placement.toUpperCase() : '—';

                if (placement === 'category') {
                    pvTarget.textContent = 'Target: ' + (targetSelect.value || '—');
                } else {
                    pvTarget.textContent = 'Target: —';
                }
            }

            function renderPackages() {
                const placement = getPlacement();
                const pkg = (PACKAGES && PACKAGES[placement]) ? PACKAGES[placement] : {};

                packageGrid.innerHTML = '';

                const daysList = Object.keys(pkg).map(d => Number(d)).sort((a,b)=>a-b);

                if (daysList.length === 0) {
                    packageGrid.innerHTML = `<div class="text-sm text-rose-700">Paket belum dikonfigurasi.</div>`;
                    return;
                }

                // kalau belum ada pilihan, auto pilih yang pertama
                if (!durationInput.value || !pkg[durationInput.value]) {
                    durationInput.value = String(daysList[0]);
                }

                daysList.forEach((days) => {
                    const price = pkg[days];
                    const isActive = (String(days) === String(durationInput.value));

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className =
                        'text-left rounded-2xl border p-4 transition ' +
                        (isActive
                            ? 'border-emerald-400 bg-emerald-50'
                            : 'border-emerald-100 bg-white hover:bg-emerald-50/40');

                    btn.innerHTML = `
                        <div class="text-xs font-semibold text-emerald-700 tracking-[0.18em]">PAKET</div>
                        <div class="mt-1 text-sm font-bold text-emerald-900">${days} hari</div>
                        <div class="mt-1 text-xs text-emerald-900/70">${formatRupiah(price)}</div>
                    `;

                    btn.addEventListener('click', () => {
                        durationInput.value = String(days);
                        renderPackages();      // re-render agar highlight pindah
                        updateBilling();
                    });

                    packageGrid.appendChild(btn);
                });
            }

            function updateBilling() {
                const placement = getPlacement();
                const days = durationInput.value ? Number(durationInput.value) : null;
                const price = (PACKAGES && PACKAGES[placement] && days) ? PACKAGES[placement][days] : null;

                billPaket.textContent = placement ? placement.toUpperCase() : '—';
                billDurasi.textContent = days ? `${days} hari` : '—';
                billTotal.textContent = price ? formatRupiah(price) : '—';
            }

            // events
            productSelect.addEventListener('change', updatePreviewProduct);

            document.querySelectorAll('input[name="placement"]').forEach((r) => {
                r.addEventListener('change', function () {
                    updateTargetVisibility();
                    updatePreviewPlacement();
                    renderPackages();
                    updateBilling();
                });
            });

            targetSelect.addEventListener('change', updatePreviewPlacement);

            // init
            updateTargetVisibility();
            updatePreviewProduct();
            updatePreviewPlacement();
            renderPackages();
            updateBilling();
        })();
    </script>
</x-app-layout>
