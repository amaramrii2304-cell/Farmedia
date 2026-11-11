<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Verifikasi Tagihan Komisi</h2>
      <form method="GET" class="flex items-center gap-2">
        <select name="status" class="rounded-lg border-gray-200 text-sm">
          @foreach(['waiting_verification'=>'Menunggu Verifikasi','unpaid'=>'Unpaid','paid'=>'Paid'] as $k=>$v)
            <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
          @endforeach
        </select>
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Filter</button>
      </form>
    </div>
  </x-slot>

  <div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

      @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ session('success') }}
        </div>
      @endif

      <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
        @if($invoices->isEmpty())
          <p class="text-sm text-gray-500">Belum ada invoice pada status ini.</p>
        @else
          <div class="divide-y divide-gray-100">
            @foreach($invoices as $inv)
              <a href="{{ route('admin.commission_invoices.show', $inv->id) }}"
                 class="block py-4 hover:bg-gray-50 rounded-xl px-2">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-semibold text-gray-900">Invoice #{{ $inv->id }} — {{ $inv->mitra->nama_toko ?? 'Mitra' }}</div>
                    <div class="text-xs text-gray-500">
                      Due: {{ optional($inv->due_date)->format('d M Y') }} · Status: {{ $inv->status }}
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs text-gray-500">Total Due</div>
                    <div class="font-bold text-emerald-700">Rp {{ number_format($inv->total_due,0,',','.') }}</div>
                  </div>
                </div>
              </a>
            @endforeach
          </div>

          <div class="mt-4">
            {{ $invoices->links() }}
          </div>
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
