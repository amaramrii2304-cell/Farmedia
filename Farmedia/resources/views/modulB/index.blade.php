{{-- resources/views/admin/products/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Produk Mitra') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($products->isEmpty())
                        <p class="text-gray-600 dark:text-gray-300">
                            Belum ada produk yang didaftarkan oleh mitra.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
                                <thead class="bg-gray-100 dark:bg-gray-700 text-xs uppercase">
                                    <tr>
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Nama Produk</th>
                                        <th class="px-4 py-3">Mitra / Toko</th>
                                        <th class="px-4 py-3">Harga</th>
                                        <th class="px-4 py-3">Stok</th>
                                        <th class="px-4 py-3">Dibuat</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
    @foreach ($products as $index => $product)
        <tr class="border-b border-gray-700">
            <td class="px-6 py-4">{{ $index + 1 }}</td>

            {{-- NAMA PRODUK --}}
            <td class="px-6 py-4 font-semibold">
                {{ $product->nama_produk ?? 'Tidak diketahui' }}
            </td>

            {{-- MITRA / TOKO --}}
            <td class="px-6 py-4">
                @if ($product->mitra)
                    <div class="font-semibold">
                        {{ $product->mitra->nama_toko }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $product->mitra->email }}
                    </div>
                @else
                    <span class="text-gray-400 italic">Tidak diketahui</span>
                @endif
            </td>

            {{-- HARGA --}}
            <td class="px-6 py-4">
                Rp {{ number_format($product->harga, 0, ',', '.') }}
            </td>

            {{-- STOK --}}
            <td class="px-6 py-4">
                {{ $product->stok }}
            </td>

            {{-- DIBUAT --}}
            <td class="px-6 py-4">
                {{ $product->created_at?->format('d-m-Y H:i') }}
            </td>

            {{-- AKSI --}}
            <td class="px-6 py-4">
                <div class="flex gap-2">
                    <a href="{{ route('admin.products.edit', $product->id) }}"
                       class="px-3 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700">
                        Edit
                    </a>

                    <form action="{{ route('admin.products.destroy', $product->id) }}"
                          method="POST"
                          onsubmit="return confirm('Hapus produk ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                            Hapus
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
</tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
