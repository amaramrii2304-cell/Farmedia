<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Resi #{{ $order->id }}</title>

    {{-- Tailwind sudah ada via Vite, tapi halaman print lebih aman pakai CSS minimal --}}
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial; color:#0f172a; }
        .box { border:1px solid #e2e8f0; border-radius:16px; padding:16px; }
        .muted { color:#64748b; }
        .row { display:flex; gap:16px; }
        .col { flex:1; }
        .h1 { font-size:18px; font-weight:800; }
        .h2 { font-size:12px; font-weight:800; letter-spacing:.18em; color:#047857; }
        .btn { display:inline-block; padding:10px 14px; border-radius:12px; border:1px solid #cbd5e1; text-decoration:none; color:#0f172a; font-weight:700; }
        .chip { display:inline-block; padding:6px 10px; border-radius:999px; background:#ecfeff; border:1px solid #a7f3d0; font-size:12px; font-weight:800; color:#065f46; }
        table { width:100%; border-collapse:collapse; }
        th, td { border-bottom:1px solid #e2e8f0; padding:10px 0; font-size:13px; vertical-align:top; }
        th { text-align:left; font-size:12px; color:#64748b; font-weight:800; letter-spacing:.12em; }
        .right { text-align:right; }

        @media print {
            .no-print { display:none !important; }
            body { margin:0; }
            @page { size: A6; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width:900px;margin:16px auto;">
    <a class="btn" href="javascript:window.print()">🖨️ Print</a>
    <span class="muted" style="margin-left:10px;">Tips: set kertas A6 / Label, lalu print.</span>
</div>

<div style="max-width:900px;margin:0 auto 24px; padding:16px;">
    <div class="box">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
            <div>
                <div class="h2">RESI PENGIRIMAN</div>
                <div class="h1">Order #{{ $order->id }}</div>
                <div class="muted" style="margin-top:6px;">
                    Dibuat: {{ optional($order->created_at)->format('d M Y, H:i') }}
                </div>
            </div>

            <div style="text-align:right;">
                <div class="chip">{{ strtoupper($order->status_order ?? 'STATUS') }}</div>
                <div class="muted" style="margin-top:6px;">
                    No Resi: <b>{{ $order->nomor_resi ?? '-' }}</b>
                </div>
            </div>
        </div>

        <div style="margin-top:16px;" class="row">
            <div class="col box" style="border-radius:14px;">
                <div class="h2">PENGIRIM</div>
                <div style="margin-top:6px;font-weight:800;">
                    {{ $order->mitra->nama_toko ?? 'Toko Mitra' }}
                </div>
                <div class="muted" style="margin-top:4px;">
                    {{ $order->mitra->alamat ?? '-' }}
                </div>
                <div class="muted" style="margin-top:4px;">
                    Telp: {{ $order->mitra->no_hp ?? '-' }}
                </div>
            </div>

            <div class="col box" style="border-radius:14px;">
                <div class="h2">PENERIMA</div>
                <div style="margin-top:6px;font-weight:800;">
                    {{ $order->user->name ?? 'Pembeli' }}
                </div>
                <div class="muted" style="margin-top:4px;">
                    {{ $order->alamat_pengiriman ?? ($order->user->alamat_lengkap ?? '-') }}
                </div>
                <div class="muted" style="margin-top:4px;">
                    Telp: {{ $order->no_hp ?? ($order->user->no_hp ?? '-') }}
                </div>
            </div>
        </div>

        <div style="margin-top:16px;" class="box" style="border-radius:14px;">
            <div class="h2">RINCIAN BARANG</div>

            <table style="margin-top:10px;">
                <thead>
                <tr>
                    <th>Produk</th>
                    <th class="right">Qty</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $items = $order->items ?? collect();
                @endphp

                @foreach($items as $it)
                    <tr>
                        <td>
                            <div style="font-weight:800;">{{ $it->product->nama_produk ?? 'Produk' }}</div>
                            <div class="muted" style="font-size:12px;">
                                Harga: Rp {{ number_format($it->harga ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="right" style="font-weight:800;">{{ $it->qty ?? 1 }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div style="display:flex; justify-content:space-between; margin-top:12px;">
                <div class="muted">Catatan:</div>
                <div style="font-weight:800;">
                    Total: Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div style="margin-top:14px; font-size:12px;" class="muted">
            Tempelkan label ini pada paket. Pastikan nama & alamat terbaca jelas.
        </div>
    </div>
</div>

</body>
</html>
