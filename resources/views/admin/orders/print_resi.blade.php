<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Resi ORD-{{ $order->id }}</title>

  <style>
    /* =========================================================
       PAPER SIZE: 100mm x 150mm (label marketplace umum)
       ========================================================= */
    @page { size: 100mm 150mm; margin: 0; }

    :root{
      --ink:#111827;
      --muted:#4b5563;
      --hair: rgba(17,24,39,.22);
      --hair2: rgba(17,24,39,.12);
    }

    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
      color: var(--ink);
      background:#fff;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* tombol back (tidak ikut print) */
    .noPrint{ position:fixed; top:10px; left:10px; z-index:10; }
    .noPrint a{
      display:inline-flex; align-items:center; gap:8px;
      padding:10px 14px; border-radius:12px;
      border:1px solid var(--hair);
      text-decoration:none; color:var(--ink);
      font-weight:800; font-size:13px;
      background:#fff;
    }
    @media print { .noPrint{ display:none; } }

    /* canvas label */
    .sheet{ width:100mm; height:150mm; padding:6mm; }
    .label{
      height:100%;
      border:2px solid var(--ink);
      border-radius:14px;
      overflow:hidden;
    }

    /* header */
    .top{
      display:flex; justify-content:space-between; align-items:flex-start;
      padding:10px 12px;
      border-bottom:1.5px solid var(--ink);
    }
    .brand{
      font-weight:950;
      letter-spacing:.10em;
      font-size:14px;
    }
    .sub{ font-size:11px; color:var(--muted); margin-top:2px; }

    .rightTop{ text-align:right; display:flex; flex-direction:column; gap:6px; align-items:flex-end; }
    .badge{
      display:inline-flex; align-items:center; justify-content:center;
      border:1.5px solid var(--ink);
      border-radius:999px;
      padding:4px 10px;
      font-size:11px;
      font-weight:950;
      letter-spacing:.06em;
    }
    .orderCode{ font-size:11px; font-weight:900; color:var(--ink); }

    /* body grid */
    .grid{
      display:grid;
      grid-template-columns: 1.18fr .82fr; /* default */
      gap:10px;
      padding:10px 12px 12px;
    }

    .panel{
      border:1px solid var(--hair);
      border-radius:12px;
      padding:10px;
    }
    .panelTitle{
      font-size:10px;
      font-weight:950;
      letter-spacing:.12em;
      text-transform:uppercase;
      margin:0 0 6px;
    }

    .resiRow{ display:flex; align-items:flex-end; justify-content:space-between; gap:10px; }
    .resiLabel{ font-size:11px; color:var(--muted); font-weight:800; }
    .resiVal{
      font-size:16px;
      font-weight:950;
      letter-spacing:.06em;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    .barcodeWrap{
      margin-top:8px;
      border:1px dashed rgba(17,24,39,.45);
      border-radius:12px;
      padding:8px;
      text-align:center;
    }
    #barcode{ width:100%; height:48px; }
    .barcodeNum{
      margin-top:6px;
      font-size:10.5px;
      font-weight:950;
      letter-spacing:.12em;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    .divider{ height:1px; background:var(--hair2); margin:10px 0; }

    .kv{ display:flex; justify-content:space-between; gap:10px; font-size:11px; }
    .kv span{ color:var(--muted); font-weight:800; }
    .kv b{ font-weight:950; }

    .card{
      border:1px solid var(--hair);
      border-radius:12px;
      padding:10px;
    }
    .toName{
      font-size:17px;
      font-weight:950;
      line-height:1.05;
      margin-top:2px;
    }
    .addr{
      font-size:11px;
      color:var(--ink);
      line-height:1.35;
      margin-top:6px;

      /* aman untuk alamat panjang */
      white-space: normal;
      word-break: break-word;
      overflow-wrap: anywhere;
    }

    .items .row{
      display:flex; justify-content:space-between; gap:10px;
      padding:5px 0;
      border-bottom:1px dashed var(--hair2);
      font-size:11px;
    }
    .items .row:last-child{ border-bottom:none; }
    .ellipsis{
      max-width:48mm;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
    }

    .note{
      font-size:10px;
      color:var(--muted);
      margin-top:8px;
    }

    .footer{
      border-top:1.5px solid var(--ink);
      padding:8px 12px;
      display:flex;
      justify-content:space-between;
      font-size:10px;
      color:var(--muted);
    }

    /* =========================================================
       MODE SWITCHING
       - default: mode-address (alamat lebih lebar)
       - mode-barcode: barcode & resi lebih dominan
       ========================================================= */

    /* ===== MODE: ALAMAT PANJANG (kanan lebih lebar) ===== */
    body.mode-address .sheet{ padding:5mm; }
    body.mode-address .grid{
      grid-template-columns: 0.95fr 1.05fr;
    }
    body.mode-address .toName{ font-size:16px; }
    body.mode-address .addr{ font-size:10.5px; }
    body.mode-address .card,
    body.mode-address .panel{ padding:9px; }
    body.mode-address .ellipsis{ max-width:58mm; }

    /* ===== MODE: BARCODE BESAR (kiri lebih lebar) ===== */
    body.mode-barcode .grid{
      grid-template-columns: 1.28fr 0.72fr;
    }
    body.mode-barcode .resiVal{
      font-size:18px;
      letter-spacing:.08em;
    }
    body.mode-barcode #barcode{ height:64px; }
    body.mode-barcode .barcodeWrap{ padding:10px; }
    body.mode-barcode .card{ padding:8px; }
    body.mode-barcode .toName{ font-size:15px; }
    body.mode-barcode .addr{ font-size:10px; line-height:1.3; }
    body.mode-barcode .items .row{ font-size:10px; padding:4px 0; }
    body.mode-barcode .ellipsis{ max-width:44mm; }
  </style>
</head>

@php
  $totalBeratGram = $order->items->sum(fn($it) => (float)($it->product->berat ?? 0) * (int)($it->jumlah ?? 0));
  $beratText = $totalBeratGram > 0 ? number_format($totalBeratGram/1000, 2, ',', '.') . ' kg' : '-';

  $isCOD = strtolower((string)($order->metode_pembayaran ?? '')) === 'cod';

  // switch mode via query: ?mode=address / ?mode=barcode
  $mode = request('mode', 'address');
  $bodyClass = $mode === 'barcode' ? 'mode-barcode' : 'mode-address';

  $orderCode = 'ORD-' . $order->id;
@endphp

<body class="{{ $bodyClass }}">
  <div class="noPrint">
    <a href="{{ route('admin.orders.show', $order->id) }}">← Kembali</a>
  </div>

  <div class="sheet">
    <div class="label">

      {{-- HEADER --}}
      <div class="top">
        <div>
          <div class="brand">FARMEDIA</div>
          <div class="sub">Label Pengiriman</div>
        </div>
        <div class="rightTop">
          <div class="badge">{{ $isCOD ? 'COD' : 'NON TUNAI' }}</div>
          <div class="orderCode">{{ $orderCode }}</div>
        </div>
      </div>

      {{-- GRID --}}
      <div class="grid">
        {{-- KIRI --}}
        <div class="panel">
          <p class="panelTitle">Nomor Resi</p>

          <div class="resiRow">
            <div class="resiLabel">Resi</div>
            <div class="resiVal">{{ $order->nomor_resi ?? '-' }}</div>
          </div>

          <div class="barcodeWrap">
            <svg id="barcode"></svg>
            <div class="barcodeNum">{{ $order->nomor_resi ?? '-' }}</div>
          </div>

          <div class="divider"></div>

          <div class="kv"><span>Metode Kirim</span><b>{{ strtoupper($order->metode_pengiriman ?? '-') }}</b></div>
          <div class="kv" style="margin-top:6px;"><span>Berat</span><b>{{ $beratText }}</b></div>
          <div class="kv" style="margin-top:6px;">
            <span>{{ $isCOD ? 'Bayar di Tempat' : 'Total Dibayar' }}</span>
            <b>Rp {{ number_format((float)($order->total_harga ?? 0), 0, ',', '.') }}</b>
          </div>

          <div class="note">
            Tempel label ini pada paket. Pastikan barcode & no resi terlihat jelas.
          </div>
        </div>

        {{-- KANAN --}}
        <div style="display:flex; flex-direction:column; gap:10px;">
          <div class="card">
            <p class="panelTitle" style="margin:0 0 6px;">Kepada</p>
            <div class="toName">{{ $order->user->name ?? 'Pembeli' }}</div>
            <div class="addr">{{ $order->alamat_pengiriman ?? '-' }}</div>
          </div>

          <div class="card">
            <p class="panelTitle" style="margin:0 0 6px;">Dari</p>
            <div style="font-weight:950; font-size:14px;">
              {{ $order->mitra->nama_toko ?? ('Mitra #' . $order->mitra_id) }}
            </div>
            <div class="addr">{{ $order->mitra->alamat ?? '-' }}</div>
          </div>

          <div class="card items">
            <p class="panelTitle" style="margin:0 0 6px;">Isi Paket</p>
            @forelse($order->items as $it)
              <div class="row">
                <div class="ellipsis">{{ $it->product->nama_produk ?? 'Produk' }}</div>
                <div><b>{{ (int)($it->jumlah ?? 0) }}x</b></div>
              </div>
            @empty
              <div class="row" style="border-bottom:none;">
                <div class="ellipsis">-</div>
                <div><b>-</b></div>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      {{-- FOOTER --}}
      <div class="footer">
        <div><b style="color:#111827;">FARMEDIA</b> • Shipping Label</div>
        <div>{{ now()->format('d M Y H:i') }}</div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <script>
    (function () {
      const resi = @json($order->nomor_resi ?? '');
      if (!resi) return;

      JsBarcode("#barcode", resi, {
        format: "CODE128",
        displayValue: false,
        height: 50,   // base height; mode-barcode override via CSS
        margin: 0
      });

      window.onload = () => window.print();
    })();
  </script>
</body>
</html>
