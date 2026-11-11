<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Invoice #{{ $invoice->id }}</h2>
        <p class="text-xs text-gray-500 mt-1">
          Mitra: <b>{{ $invoice->mitra->nama_toko ?? '-' }}</b> · Status: <b>{{ $invoice->status }}</b>
        </p>
      </div>
      <a href="{{ route('admin.commission_invoices.index', ['status' => request('status','waiting_verification')]) }}"
         class="px-4 py-2 rounded-xl border bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
        ← Kembali
      </a>
    </div>
  </x-slot>

  <div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

      @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {{ session('error') }}
        </div>
      @endif
      @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ session('success') }}
        </div>
      @endif

      <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Pokok</p>
            <p class="mt-1 font-bold">Rp {{ number_format($invoice->amount,0,',','.') }}</p>
          </div>
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Denda</p>
            <p class="mt-1 font-bold text-amber-700">Rp {{ number_format($invoice->penalty,0,',','.') }}</p>
          </div>
          <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
            <p class="text-xs text-emerald-700">Total Due</p>
            <p class="mt-1 font-extrabold text-emerald-800">Rp {{ number_format($invoice->total_due,0,',','.') }}</p>
          </div>
        </div>

        <div class="mt-4 text-sm">
          <p class="font-semibold text-gray-800">Bukti pembayaran</p>
          @if($invoice->payment_proof_path)
            <a class="text-emerald-700 underline"
               href="{{ asset('storage/' . $invoice->payment_proof_path) }}"
               target="_blank">
              Lihat bukti
            </a>
          @else
            <p class="text-gray-500">Belum ada bukti.</p>
          @endif

          @if($invoice->notes)
            <p class="mt-2 text-xs text-gray-600"><b>Catatan:</b> {{ $invoice->notes }}</p>
          @endif
        </div>

        @if($invoice->status === 'waiting_verification')
          <div class="mt-5 flex flex-col sm:flex-row gap-2">
            <form method="POST" action="{{ route('admin.commission_invoices.approve', $invoice->id) }}">
              @csrf
              <button class="px-5 py-2.5 rounded-full bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                Approve (Lunas)
              </button>
            </form>

            <form method="POST" action="{{ route('admin.commission_invoices.reject', $invoice->id) }}" class="flex-1">
              @csrf
              <input type="text" name="notes" required
                     placeholder="Alasan reject (wajib)"
                     class="w-full rounded-full border-gray-200 text-sm px-4 py-2.5">
              <button class="mt-2 px-5 py-2.5 rounded-full border border-rose-300 bg-rose-50 text-rose-700 text-sm font-semibold hover:bg-rose-100">
                Reject
              </button>
            </form>
          </div>
        @endif
      </div>

      <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-900 mb-3">Order yang termasuk invoice</p>
        <div class="space-y-2">
          @foreach($invoice->orders as $o)
            @php $hutang = (float)$o->komisi_produk + (float)$o->komisi_ongkir; @endphp
            <div class="rounded-xl border border-gray-100 px-4 py-3 flex items-center justify-between text-sm">
              <div>
                <div class="font-semibold">ORD-{{ $o->id }}</div>
                <div class="text-xs text-gray-500">
                  {{ $o->user->name ?? '-' }} · {{ $o->created_at?->format('d M Y H:i') }}
                </div>
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
  </div>
</x-app-layout>
