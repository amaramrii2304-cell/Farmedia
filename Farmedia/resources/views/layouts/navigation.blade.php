<nav x-data="{ open: false }"
     class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-emerald-500 text-white shadow">

    @php
        use Illuminate\Support\Facades\Auth;

        $user = Auth::user();

        // Default nilai
        $userNotifCount = 0;
        $notifLabel = 0;
        $recentOrders = collect();

        // Notifikasi khusus pembeli (role user)
        if ($user && $user->role === 'user') {
            // Pesanan aktif (belum selesai / dibatalkan)
            $userNotifCount = \App\Models\Order::where('user_id', $user->id)
                ->whereNotIn('status_order', ['selesai', 'dibatalkan'])
                ->count();

            $notifLabel = $userNotifCount > 9 ? '9+' : $userNotifCount;

            // 5 update pesanan terbaru
            $recentOrders = \App\Models\Order::with(['mitra', 'items.product'])
                ->where('user_id', $user->id)
                ->orderByDesc('updated_at')
                ->take(5)
                ->get();
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- BAR ATAS ALA SHOPEE: IKUTI KAMI + NOTIFIKASI (KHUSUS USER) --}}
        @if($user && $user->role === 'user')
            <div class="flex justify-between items-center text-xs py-1">
                {{-- Ikuti kami di --}}
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline">Ikuti kami di:</span>

                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/farmedia2025" target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-center w-6 h-6 rounded-full bg-white/10 hover:bg-white/20">
                        {{-- icon instagram sederhana --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.7">
                            <rect x="4" y="4" width="16" height="16" rx="5" ry="5"></rect>
                            <circle cx="12" cy="12" r="3.2"></circle>
                            <circle cx="17" cy="7" r="1"></circle>
                        </svg>
                    </a>

                    {{-- TikTok --}}
                    <a href="https://www.tiktok.com" target="_blank"
                       class="flex items-center justify-center w-6 h-6 rounded-full bg-white/10 hover:bg-white/20">
                        {{-- icon tiktok sederhana --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             class="w-3.5 h-3.5" fill="currentColor">
                            <path d="M16.75 7.5a4.5 4.5 0 0 0 3 1.12V11a6.1 6.1 0 0 1-3-.82v4.07A4.75 4.75 0 1 1 11 9.75v2.12a2.25 2.25 0 1 0 1.5 2.12V4.5h2.25v3z"/>
                        </svg>
                    </a>
                </div>

                {{-- Notifikasi (hover untuk buka panel) --}}
                <div class="flex items-center gap-4">
                    <div class="relative group">
                        <button type="button"
                                class="flex items-center gap-1 hover:underline">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/>
                                <path d="M10 19a2 2 0 0 0 4 0"/>
                            </svg>
                            <span class="hidden sm:inline">Notifikasi</span>

                            {{-- badge angka notifikasi --}}
                            @if($userNotifCount > 0)
                                <span class="ml-1 inline-flex items-center justify-center
                                             min-w-[16px] h-4 px-1 rounded-full bg-red-500
                                             text-[10px] font-semibold text-white">
                                    {{ $notifLabel }}
                                </span>
                            @endif
                        </button>

                        {{-- Panel notifikasi (REAL dari orders) --}}
                        <div
                            class="absolute right-0 mt-2 w-80 bg-white text-gray-800 rounded-lg shadow-xl py-2
                                   opacity-0 pointer-events-none transform translate-y-2
                                   group-hover:opacity-100 group-hover:pointer-events-auto group-hover:translate-y-0
                                   transition duration-150 z-30">

                            <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                <span class="text-sm font-semibold">Notifikasi Pesanan</span>

                                <a href="{{ route('user.notifications.index') }}"
                                   class="text-[10px] text-emerald-600 font-medium hover:underline">
                                    Lihat semua
                                </a>
                            </div>

                            @if($recentOrders->isEmpty())
                                <div class="px-4 py-3 text-[11px] text-gray-500">
                                    Belum ada notifikasi pesanan.
                                </div>
                            @else
                                @php
                                    $statusMap = [
                                        'menunggu_konfirmasi' => ['label' => 'Menunggu konfirmasi mitra'],
                                        'diproses'           => ['label' => 'Pesanan sedang diproses'],
                                        'dikemas'            => ['label' => 'Pesanan sedang dikemas'],
                                        'dikirim'            => ['label' => 'Pesanan dikirim'],
                                        'siap_diambil'       => ['label' => 'Pesanan siap diambil'],
                                        'selesai'            => ['label' => 'Pesanan selesai'],
                                        'rejected'           => ['label' => 'Pesanan ditolak mitra'],
                                        'dibatalkan'         => ['label' => 'Pesanan dibatalkan'],
                                        'pending_cancel'     => ['label' => 'Pengajuan pembatalan diproses'],
                                    ];
                                @endphp

                                <div class="max-h-64 overflow-y-auto">
                                    @foreach($recentOrders as $order)
                                        @php
                                            $status = $order->status_order;
                                            $info   = $statusMap[$status] ?? ['label' => ucfirst(str_replace('_',' ',$status))];

                                            $produkList = $order->items->map(function($item) {
                                                return $item->product->nama_produk ?? 'Produk dihapus';
                                            })->take(2)->implode(', ');

                                            if ($order->items->count() > 2) {
                                                $produkList .= ' + ' . ($order->items->count() - 2) . ' produk lain';
                                            }
                                        @endphp

                                        <a href="{{ route('orders.show', $order->id) }}"
                                           class="block px-4 py-3 hover:bg-gray-50 cursor-pointer border-t border-gray-100 first:border-t-0">
                                            <p class="text-xs font-semibold text-gray-800 flex items-center justify-between">
                                                <span>{{ $info['label'] }}</span>
                                                <span class="text-[10px] text-gray-400">
                                                    {{ $order->updated_at?->diffForHumans() }}
                                                </span>
                                            </p>
                                            <p class="text-[11px] text-gray-500 mt-0.5">
                                                ORD-{{ $order->id }} • {{ $produkList }}
                                            </p>
                                            <p class="text-[11px] text-gray-500 mt-0.5">
                                                Toko: {{ $order->mitra->nama_toko ?? '-' }}
                                            </p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- BAR UTAMA (logo, search, cart, menu, akun) --}}
        <div class="flex justify-between h-16">
            {{-- KIRI (logo + nav / search) --}}
            <div class="flex items-center flex-1">
                @if($user && $user->role === 'user')
                    {{-- === NAVBAR USER / PEMBELI – LOGO + SEARCH + CART + MENU === --}}
                    <div class="flex items-center gap-4 w-full">
                        {{-- Logo --}}
                        <div class="shrink-0">
                            <a href="{{ route('user.beranda') }}" class="flex items-center gap-2">
                                <x-application-logo class="block h-28 w-auto fill-current text-white" />
                                <span class="hidden sm:inline-block font-semibold text-lg tracking-wide">
                                    FARMEDIA
                                </span>
                            </a>
                        </div>

                        {{-- Search di tengah --}}
                        <form action="{{ route('user.beranda') }}" method="GET"
                              class="flex-1 hidden md:flex">
                            <div class="flex items-stretch w-full bg-white rounded-md overflow-hidden">
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ request('q') }}"
                                    placeholder="Cari produk pertanian..."
                                    class="flex-1 px-4 py-2 text-sm text-gray-800 focus:outline-none">
                                <button type="submit"
                                        class="px-4 flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
                                    </svg>
                                </button>
                            </div>
                        </form>

                        {{-- Keranjang di kanan search --}}
                        <a href="{{ route('user.cart.index') }}"
                           class="relative flex items-center justify-center ml-2">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-7 w-7"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m12-9l2 9m-8-4h4" />
                            </svg>

                            {{-- ✅ FIX: badge keranjang ambil dari DB cart_items, bukan session --}}
                            @php
                                $count = 0;
                                if ($user) {
                                    $count = (int) \App\Models\CartItem::where('user_id', $user->id)->sum('qty');
                                }
                            @endphp

                            @if ($count > 0)
                                <span class="absolute -top-1 -right-2
                                             bg-red-500 text-white text-[10px] min-w-[16px] min-h-[16px]
                                             px-1 rounded-full flex items-center justify-center">
                                    {{ $count > 99 ? '99+' : $count }}
                                </span>
                            @endif
                        </a>

                        {{-- Menu teks di kanan --}}
                        <div class="hidden lg:flex items-center gap-4 text-sm font-medium ml-4">
                            <a href="{{ route('user.beranda') }}"
                               class="hover:underline {{ request()->routeIs('user.beranda') ? 'underline font-semibold' : '' }}">
                                Beranda
                            </a>
                            <a href="{{ route('orders.index') }}"
                               class="hover:underline {{ request()->routeIs('orders.*') ? 'underline font-semibold' : '' }}">
                                Pesanan Saya
                            </a>
                            <a href="{{ route('user.edukasi.index') }}"
                               class="hover:underline {{ request()->routeIs('user.edukasi.*') ? 'underline font-semibold' : '' }}">
                                Edukasi
                            </a>
                            <a href="{{ route('profile.edit') }}"
                               class="hover:underline {{ request()->routeIs('profile.edit') ? 'underline font-semibold' : '' }}">
                                Profil
                            </a>
                        </div>
                    </div>
                @else
                    {{-- === NAV ADMIN & MITRA – STRUKTUR ASLI === --}}
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="
                                {{ Auth::user()->role === 'admin'
                                    ? route('admin.dashboard')
                                    : (Auth::user()->role === 'mitra'
                                        ? route('mitra.dashboard')
                                        : route('user.beranda')) }}">
                                <x-application-logo class="block h-9 w-auto fill-current text-white" />
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            @if(Auth::user()->role === 'admin')
                                <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                                    {{ __('Dashboard') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.pengguna.index')" :active="request()->routeIs('admin.pengguna.*')">
                                    {{ __('Pengguna') }}
                                </x-nav-link>
                                <x-nav-link :href="route('modulB.index')" :active="request()->routeIs('modulB.*')">
                                    {{ __('Produk') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.edukasi.index')" :active="request()->routeIs('admin.edukasi.*')">
                                    {{ __('Edukasi') }}
                                </x-nav-link>
                                <x-nav-link :href="route('modulD.index')" :active="request()->routeIs('modulD.*')">
                                    {{ __('Tentang') }}
                                </x-nav-link>
                            @elseif(Auth::user()->role === 'mitra')
                                <x-nav-link :href="route('mitra.dashboard')" :active="request()->routeIs('mitra.dashboard')">
                                    {{ __('Dashboard') }}
                                </x-nav-link>
                                <x-nav-link :href="route('mitra.products.index')" :active="request()->routeIs('mitra.products.*')">
                                    {{ __('Produk Saya') }}
                                </x-nav-link>
                                <x-nav-link :href="route('mitra.orders.index')" :active="request()->routeIs('mitra.orders.*')">
                                    {{ __('Daftar Pesanan') }}
                                </x-nav-link>
                                <x-nav-link :href="route('mitra.profile.edit')" :active="request()->routeIs('mitra.profile.*')">
                                    {{ __('Profil Toko') }}
                                </x-nav-link>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- KANAN – DROPDOWN USER --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4
                                   font-medium rounded-md text-emerald-900 bg-white/90 hover:bg-white
                                   focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-white
                               hover:bg-emerald-800 focus:outline-none focus:bg-emerald-800
                               focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white text-gray-800">
        <div class="pt-2 pb-3 space-y-1">
            @if(Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.pengguna.index')" :active="request()->routeIs('admin.pengguna.*')">
                    {{ __('Pengguna') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('modulB.index')" :active="request()->routeIs('modulB.*')">
                    {{ __('Produk') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.edukasi.index')" :active="request()->routeIs('admin.edukasi.*')">
                    {{ __('Edukasi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('modulD.index')" :active="request()->routeIs('modulD.*')">
                    {{ __('Tentang') }}
                </x-responsive-nav-link>
            @elseif(Auth::user()->role === 'mitra')
                <x-responsive-nav-link :href="route('mitra.dashboard')" :active="request()->routeIs('mitra.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.products.index')" :active="request()->routeIs('mitra.products.*')">
                    {{ __('Produk Saya') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.orders.index')" :active="request()->routeIs('mitra.orders.*')">
                    {{ __('Daftar Pesanan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('mitra.profile.edit')" :active="request()->routeIs('mitra.profile.*')">
                    {{ __('Profil Toko') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('user.beranda')" :active="request()->routeIs('user.beranda')">
                    {{ __('Beranda') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                    {{ __('Pesanan Saya') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.edukasi.index')" :active="request()->routeIs('user.edukasi.*')">
                    {{ __('Edukasi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                    {{ __('Profil') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
