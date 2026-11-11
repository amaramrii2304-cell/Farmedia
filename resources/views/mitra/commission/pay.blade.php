<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bayar Hutang Komisi</h2>
        <p class="text-xs text-gray-500 mt-1">
          Invoice #{{ $invoice->id }} · Jatuh tempo: <b>{{ optional($invoice->due_date)->format('d M Y') }}</b>
        </p>
      </div>
      <a href="{{ route('mitra.commission.index') }}"
         class="px-4 py-2 rounded-xl border bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
        ← Kembali
      </a>
    </div>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">

      @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {{ session('error') }}
        </div>
      @endif

      <div class="bg-white border border-emerald-50 rounded-2xl shadow-sm p-5">
        <p class="text-xs font-semibold tracking-wide text-emerald-700 uppercase">Ringkasan Tagihan</p>

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Pokok Hutang</p>
            <p class="mt-1 font-bold text-gray-900">Rp {{ number_format($invoice->amount,0,',','.') }}</p>
          </div>
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Denda</p>
            <p class="mt-1 font-bold text-amber-700">Rp {{ number_format($invoice->penalty,0,',','.') }}</p>
            @if($invoice->isOverdue())
              <p class="mt-1 text-[11px] text-amber-600">Terlambat bayar (maks denda 10%).</p>
            @endif
          </div>
          <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
            <p class="text-xs text-emerald-700">Total Harus Dibayar</p>
            <p class="mt-1 text-lg font-extrabold text-emerald-800">Rp {{ number_format($invoice->total_due,0,',','.') }}</p>
          </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-100 p-4 text-sm">
          <p class="font-semibold text-gray-800">Transfer ke rekening platform</p>
          <p class="mt-1 text-gray-600">
            Bank: <b>{{ $bank['nama'] }}</b><br>
            No Rekening: <b>{{ $bank['nomor'] }}</b><br>
            A/N: <b>{{ $bank['pemilik'] }}</b>
          </p>
        </div>

        <div class="mt-4">
          <p class="text-sm font-semibold text-gray-800 mb-2">Daftar pesanan penyebab hutang</p>
          <div class="space-y-2">
            @foreach($invoice->orders as $o)
              @php $hutang = (float) ($o->computed_hutang ?? ((float)$o->komisi_produk + (float)$o->komisi_ongkir)); @endphp
              <div class="rounded-xl border border-gray-100 px-4 py-3 flex items-center justify-between text-sm">
                <div>
                  <div class="font-semibold text-gray-900">ORD-{{ $o->id }}</div>
                  <div class="text-xs text-gray-500">{{ $o->created_at?->format('d M Y H:i') }}</div>
                </div>
                <div class="text-right">
                  <div class="text-xs text-gray-500">Hutang</div>
                  <div class="font-bold text-amber-700">Rp {{ number_format($hutang,0,',','.') }}</div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('mitra.commission.pay.submit') }}" enctype="multipart/form-data"
            class="bg-white border border-emerald-50 rounded-2xl shadow-sm p-5 space-y-3">
        @csrf

        <p class="text-xs font-semibold tracking-wide text-emerald-700 uppercase">Kirim Bukti Pembayaran</p>

        <div>
          <label class="text-xs font-medium text-gray-600">Metode</label>
          <select name="payment_method"
                  class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            <option value="transfer" selected>Transfer Bank</option>
          </select>
          @error('payment_method') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="text-xs font-medium text-gray-600">Upload bukti (jpg/png/pdf)</label>
          <input type="file" name="payment_proof"
                 class="mt-1 w-full rounded-lg border-gray-200 text-sm">
          @error('payment_proof') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="text-xs font-medium text-gray-600">Catatan (opsional)</label>
          <textarea name="notes" rows="3"
                    class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
          @error('notes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="w-full sm:w-auto px-5 py-2.5 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
          Kirim Bukti Pembayaran
        </button>

        <p class="text-[11px] text-gray-500">
          Setelah dikirim, status akan menjadi <b>Menunggu Verifikasi Admin</b>.
        </p>
      </form>

    </div>
  </div>
</x-app-layout>
