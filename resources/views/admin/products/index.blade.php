<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Produk (Semua Mitra)
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2 px-3">#</th>
                        <th class="py-2 px-3">Nama Produk</th>
                        <th class="py-2 px-3">Mitra / Toko</th>
                        <th class="py-2 px-3">Harga</th>
                        <th class="py-2 px-3">Stok</th>
                        {{-- KOLOM BARU: RATING --}}
                        <th class="py-2 px-3">Rating</th>
                        <th class="py-2 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $avgRating    = $product->avg_rating ? round($product->avg_rating, 1) : null;
                            $reviewsCount = $product->reviews_count ?? 0;
                        @endphp

                        <tr class="border-b border-gray-100">
                            <td class="py-2 px-3">{{ $loop->iteration }}</td>
                            <td class="py-2 px-3">{{ $product->nama_produk }}</td>
                            <td class="py-2 px-3">{{ $product->mitra->nama_toko ?? '-' }}</td>
                            <td class="py-2 px-3">Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                            <td class="py-2 px-3">{{ $product->stok }}</td>

                            {{-- ISI KOLOM RATING --}}
                            <td class="py-2 px-3">
                                @if($reviewsCount > 0)
                                    <div class="inline-flex items-center gap-1 text-xs">
                                        <span class="text-amber-400">★</span>
                                        <span class="font-semibold text-gray-800">
                                            {{ number_format($avgRating, 1) }}
                                        </span>
                                        <span class="text-gray-400">
                                            ({{ $reviewsCount }})
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">Belum ada ulasan</span>
                                @endif
                            </td>

                            <td class="py-2 px-3 text-center flex gap-2 justify-center">
                                <!-- Tombol Edit -->
                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                   class="px-3 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700">
                                    Edit
                                </a>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-500">
                                Belum ada produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
