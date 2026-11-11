<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Produk Saya (Mitra)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Daftar Produk Milik: {{ $user->name }}</h3>
                        <a href="{{ route('mitra.products.create') }}"
                           class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                            + Tambah Produk
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="px-3 py-2 text-left">#</th>
                                    <th class="px-3 py-2 text-left">Foto</th>
                                    <th class="px-3 py-2 text-left">Nama Produk</th>
                                    <th class="px-3 py-2 text-left">Kategori</th>
                                    <th class="px-3 py-2 text-left">Harga</th>
                                    <th class="px-3 py-2 text-left">Stok</th>
                                    <th class="px-3 py-2 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $index => $product)
                                    <tr class="border-b border-gray-700">
                                        <td class="px-3 py-2 align-top">
                                            {{ $products->firstItem() + $index }}
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            @if($product->foto_produk)
                                                <img src="{{ asset('storage/'.$product->foto_produk) }}"
                                                     alt="Foto {{ $product->nama_produk }}"
                                                     class="w-16 h-16 object-cover rounded">
                                            @else
                                                <span class="text-xs text-gray-400">Belum ada foto</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="font-semibold">{{ $product->nama_produk }}</div>
                                            <div class="text-xs text-gray-400 line-clamp-2">
                                                {{ $product->deskripsi_produk }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            {{ $product->kategori_produk ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            Rp {{ number_format($product->harga, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            {{ $product->stok }}
                                        </td>
                                        <td class="px-3 py-2 align-top space-x-1">
                                            <a href="{{ route('mitra.products.edit', $product) }}"
                                               class="inline-block px-2 py-1 text-xs bg-yellow-500 text-white rounded">
                                                Edit
                                            </a>
                                            <form action="{{ route('mitra.products.destroy', $product) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-2 py-1 text-xs bg-red-600 text-white rounded">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-4 text-center text-gray-400">
                                            Belum ada data produk. Tambahkan produk pertama Anda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
