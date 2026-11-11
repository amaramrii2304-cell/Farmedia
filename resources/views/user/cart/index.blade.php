<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Keranjang Belanja</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto">

            @if(empty($cart))
                <div class="bg-white p-6 rounded shadow text-center">
                    <p class="text-gray-600">Keranjang masih kosong.</p>
                    <a href="{{ route('user.beranda') }}"
                       class="mt-3 inline-block px-4 py-2 bg-indigo-600 text-white rounded">
                        Belanja Sekarang
                    </a>
                </div>
            @else
                <form action="{{ route('checkout.fromCart') }}" method="POST">
                    @csrf

                    <div class="bg-white p-6 rounded shadow space-y-4">

                        @foreach($cart as $id => $item)
                            <div class="flex gap-4 border-b pb-4">

                                <input
                                    type="checkbox"
                                    name="selected_items[]"
                                    value="{{ $id }}"
                                    class="w-5 h-5 mt-6"
                                    {{-- kalau kamu MAU ikut DB selected, aktifkan ini: --}}
                                    {{-- {{ !empty($item['selected']) ? 'checked' : '' }} --}}
                                >

                                <img src="{{ asset('storage/'.$item['foto']) }}"
                                     class="w-20 h-20 rounded object-cover">

                                <div class="flex-1">
                                    <p class="font-semibold">{{ $item['nama'] }}</p>
                                    <p class="text-sm">{{ $item['qty'] }} × Rp {{ number_format($item['harga']) }}</p>
                                </div>

                                {{-- Button hapus --}}
                                <button type="submit"
                                        formaction="{{ route('cart.remove', $id) }}"
                                        formmethod="POST"
                                        class="text-red-500">
                                    Hapus
                                </button>
                            </div>
                        @endforeach

                        <button type="submit"
                                class="block w-full text-center py-3 bg-green-600 text-white rounded-lg mt-4">
                            Lanjut ke Checkout
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
