<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <meta name="description" content="Farmedia - Platform integrasi cerdas untuk mitra pertanian, kelola produk, laporan penjualan, komisi, dan perluas jangkauan pasar agribisnis Anda dengan mudah.">

    <title>{{ config('app.name', 'Farmedia - Mitra Pertanian Cerdas') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"></noscript>

    <link rel="preload" href="https://overkill-snorkel-curvature.ngrok-free.dev/build/assets/app-CJy8ASEk.js" as="script">
    <link rel="preload" href="https://overkill-snorkel-curvature.ngrok-free.dev/build/assets/app-Bsvo8Ezg.css" as="style">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
@php
    $user = auth()->user();

    $routeName = request()->route()?->getName() ?? '';
    $menuBase  = 'flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium transition shadow-sm';
    $inactive  = 'text-emerald-900 hover:bg-emerald-800 hover:text-white';
    $active    = 'bg-emerald-600 text-white shadow-md';
@endphp

{{-- =========================================================
   LAYOUT DENGAN SIDEBAR (ADMIN & MITRA)
   ========================================================== --}}
@if($user && in_array($user->role, ['admin','mitra']))
    <div class="min-h-screen bg-emerald-50 flex">

        {{-- ================== SIDEBAR ================== --}}
        <aside class="w-60 shrink-0 bg-white border-r border-emerald-100 flex flex-col">
            {{-- Logo --}}
            <div class="px-6 py-1 flex items-center justify-center h-24 mb-2">
                <a href="{{ $user->role === 'admin' ? route('admin.dashboard') : route('mitra.dashboard') }}"
                   class="flex items-center gap-3">
                    <x-application-logo class="h-40 w-auto" />
                </a>
            </div>

            <nav class="flex-1 px-4 pt-3 py-6 space-y-8 text-sm">
                {{-- ================== MENU UTAMA ================== --}}
                <div>
                    <p class="text-[11px] font-semibold text-emerald-900 tracking-[0.15em] mb-3">MENU</p>

                    {{-- ================== MENU ADMIN ================== --}}
                    @if($user->role === 'admin')

                        {{-- Dashboard --}}
                        <a href="{{ route('admin.dashboard') }}"
                           class="{{ $menuBase }} {{ request()->routeIs('admin.dashboard') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/>
                                </svg>
                            </span>
                            <span>Dashboard</span>
                        </a>

                        {{-- User Management --}}
                        <a href="{{ route('admin.pengguna.index') }}"
                           class="mt-2 {{ $menuBase }} {{ request()->routeIs('admin.pengguna.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('admin.pengguna.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20v-2a4 4 0 00-3-3.87M9 20v-2a4 4 0 013-3.87M12 11a3 3 0 100-6 3 3 0 000 6zm6 3a3 3 0 10-3-3"/>
                                </svg>
                            </span>
                            <span>User Management</span>
                        </a>

                        {{-- Content Management --}}
                        <a href="{{ route('admin.edukasi.index') }}"
                           class="mt-2 {{ $menuBase }} {{ (request()->routeIs('admin.edukasi.*') || request()->routeIs('modulB.*')) ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ (request()->routeIs('admin.edukasi.*') || request()->routeIs('modulB.*')) ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h4m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span>Content Management</span>
                        </a>

                        {{-- Komisi Platform (dropdown) --}}
						@php
						  $isKomisiActive = request()->routeIs('admin.commissions.*') || request()->routeIs('admin.commission_invoices.*');
						@endphp

						<details class="mt-2 group" {{ $isKomisiActive ? 'open' : '' }}>
						  <summary class="{{ $menuBase }} {{ $isKomisiActive ? $active : $inactive }} cursor-pointer list-none">
							<span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
							  {{ $isKomisiActive ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
							  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
									  d="M4 7c0-1.1 3.1-2 7-2s7 0.9 7 2-3.1 2-7 2-7-0.9-7-2zm0 5c0-1.1 3.1-2 7-2m-7 7c0-1.1 3.1-2 7-2m0-10v14m7-9v5"/>
							  </svg>
							</span>

							<span class="flex-1">Komisi Platform</span>

							{{-- caret --}}
							<span class="text-emerald-800/70 group-open:rotate-180 transition">
							  ▾
							</span>
						  </summary>

						  {{-- isi dropdown --}}
						  <div class="mt-2 pl-10 space-y-1">
							<a href="{{ route('admin.commissions.index') }}"
							   class="block rounded-xl px-3 py-2 text-sm
									  {{ request()->routeIs('admin.commissions.*')
										  ? 'bg-emerald-50 text-emerald-800 font-semibold'
										  : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
							  Ringkasan Komisi
							</a>

							<a href="{{ route('admin.commission_invoices.index') }}"
							   class="block rounded-xl px-3 py-2 text-sm
									  {{ request()->routeIs('admin.commission_invoices.*')
										  ? 'bg-emerald-50 text-emerald-800 font-semibold'
										  : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
							  Invoice Komisi
							</a>
						  </div>
						</details>

                        {{-- Data Pesanan --}}
                        <a href="{{ route('admin.orders.index') }}"
                           class="mt-2 {{ $menuBase }} {{ request()->routeIs('admin.orders.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('admin.orders.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5h6m-6 4h6m-6 4h6M7 5h.01M7 9h.01M7 13h.01M5 3h14a2 2 0 012 2v16a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                </svg>
                            </span>
                            <span>Data Pesanan</span>
                        </a>

                        {{-- Iklan / Promosi (ADMIN) --}}
                        <a href="{{ route('admin.ads.index') }}"
                            class="mt-2 {{ $menuBase }} {{ request()->routeIs('admin.ads.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('admin.ads.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5l10 3v8l-10 3V5zM4 10v4a2 2 0 002 2h1V8H6a2 2 0 00-2 2z"/>
                                </svg>
                            </span>
                            <span>Iklan / Promosi</span>
                        </a>

                        <a href="{{ route('admin.cod.index') }}"
                            class="mt-2 {{ $menuBase }} {{ request()->routeIs('admin.cod.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('admin.cod.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                    💵
                            </span>
                            <span>Transaksi COD</span>
                        </a>

                        {{-- Notification --}}
                        <a href="{{ route('admin.notifications.index') }}"
                           class="mt-2 {{ $menuBase }} {{ request()->routeIs('admin.notifications.index') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('admin.notifications.index') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                                </svg>
                            </span>
                            <span>Notification</span>
                        </a>

                    {{-- ================== MENU MITRA ================== --}}
                    @else

                        {{-- Dashboard --}}
                        <a href="{{ route('mitra.dashboard') }}"
                           class="{{ $menuBase }} {{ request()->routeIs('mitra.dashboard') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('mitra.dashboard') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                📊
                            </span>
                            <span>Dashboard</span>
                        </a>

                        {{-- Produk --}}
                        @php
						  $produkOpen = request()->routeIs('mitra.products.*') || request()->routeIs('mitra.reviews.*');
						@endphp

						<div x-data="{ open: {{ $produkOpen ? 'true' : 'false' }} }" class="space-y-2">
						  {{-- Tombol utama Produk --}}
						  <button type="button"
								  @click="open = !open"
								  class="{{ $menuBase }} {{ $produkOpen ? $active : $inactive }}">
							  <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
								  {{ $produkOpen ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
								  📦
							  </span>

							  <span>Produk</span>

							  <span class="ml-auto text-emerald-900/60"
									:class="open ? 'rotate-180' : ''"
									style="transition: transform .2s;">
								▾
							  </span>
						  </button>

						  {{-- Dropdown items --}}
						  <div x-cloak x-show="open" class="pl-11 space-y-2">
							  <a href="{{ route('mitra.products.index') }}"
								 class="block rounded-xl px-3 py-2 text-sm font-medium border transition
										{{ request()->routeIs('mitra.products.*')
											? 'bg-emerald-50 text-emerald-800 border-emerald-200'
											: 'bg-white text-emerald-900 border-emerald-100 hover:bg-emerald-50' }}">
								  Daftar Produk
							  </a>

							  <a href="{{ route('mitra.reviews.index') }}"
								 class="block rounded-xl px-3 py-2 text-sm font-medium border transition
										{{ request()->routeIs('mitra.reviews.*')
											? 'bg-emerald-50 text-emerald-800 border-emerald-200'
											: 'bg-white text-emerald-900 border-emerald-100 hover:bg-emerald-50' }}">
								  Ulasan Produk
							  </a>
						  </div>
						</div>

                        {{-- Badge pesanan baru --}}
                        @php
                            $mitraPendingOrderCount = \App\Models\Order::where('mitra_id', $user->mitra_id ?? null)
                                ->where('status_order', 'menunggu_konfirmasi')
                                ->count();
                        @endphp

                        {{-- Daftar Pesanan --}}
                        <a href="{{ route('mitra.orders.index') }}"
                           class="mt-2 {{ $menuBase }} {{ request()->routeIs('mitra.orders.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('mitra.orders.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                📝
                            </span>
                            <span>Daftar Pesanan</span>

                            @if($mitraPendingOrderCount > 0)
                                <span class="ml-auto inline-flex items-center justify-center min-w-[18px] h-5 rounded-full
                                             bg-red-500 text-white text-[10px] font-semibold">
                                    {{ $mitraPendingOrderCount > 9 ? '9+' : $mitraPendingOrderCount }}
                                </span>
                            @endif
                        </a>

                        {{-- Laporan Penjualan --}}
                        <a href="{{ route('mitra.sales.index') }}"
                           class="mt-2 {{ $menuBase }} {{ request()->routeIs('mitra.sales.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('mitra.sales.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                📈
                            </span>
                            <span>Laporan Penjualan</span>
                        </a>

                        {{-- Komisi & Pendapatan --}}
						<a href="{{ route('mitra.commission.index') }}"
						   class="mt-2 {{ $menuBase }} {{ request()->routeIs('mitra.commission.*') ? $active : $inactive }}">
						  <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
							  {{ request()->routeIs('mitra.commission.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
							💰
						  </span>
						  <span>Komisi</span>
						</a>


                        {{-- Iklan / Promosi (MITRA) --}}
                        <a href="{{ route('mitra.ads.index') }}"
                            class="mt-2 {{ $menuBase }} {{ request()->routeIs('mitra.ads.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('mitra.ads.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                📣
                            </span>
                            <span>Iklan / Promosi</span>
                        </a>

                    @endif
                </div>

                {{-- ================== PROFIL & LOGOUT ================== --}}
                <div class="pt-4 border-t border-emerald-200/70">
                    <p class="text-[11px] font-semibold text-emerald-900 tracking-[0.15em] mb-3">PROFILE</p>

                    {{-- Pengaturan --}}
                    @if($user->role === 'admin')
                        <a href="{{ route('profile.edit') }}"
                           class="{{ $menuBase }} {{ request()->routeIs('profile.edit') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('profile.edit') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11.3 3.06a1 1 0 011.4 0l1.2 1.2a1 1 0 00.9.27l1.6-.3a1 1 0 011.1.58l.7 1.5a1 1 0 01-.2 1.1l-1 1a1 1 0 000 1.42l1 1a1 1 0 01.2 1.1l-.7 1.5a1 1 0 01-1.1.58l-1.6-.3a1 1 0 00-.9.27l-1.2 1.2a1 1 0 01-1.4 0l-1.2-1.2a1 1 0 00-.9-.27l-1.6.3a1 1 0 01-1.1-.58l-.7-1.5a1 1 0 01.2-1.1l1-1a1 1 0 000-1.42l-1-1a1 1 0 01-.2-1.1l.7-1.5a1 1 0 011.1-.58l1.6.3a1 1 0 00.9-.27l1.2-1.2z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </span>
                            <span>Pengaturan</span>
                        </a>
                    @else
                        <a href="{{ route('mitra.profile.edit') }}"
                           class="{{ $menuBase }} {{ request()->routeIs('mitra.profile.*') ? $active : $inactive }}">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg
                                {{ request()->routeIs('mitra.profile.*') ? 'bg-emerald-500/80 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                ⚙️
                            </span>
                            <span>Pengaturan</span>
                        </a>
                    @endif

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="{{ $menuBase }} text-red-700 hover:bg-red-50">
                            <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg bg-red-100 text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12H3m0 0l4-4m-4 4l4 4m4-10h6a2 2 0 012 2v8a2 2 0 01-2 2h-6"/>
                                </svg>
                            </span>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        {{-- ================== AREA KONTEN (HEADER + SLOT) ================== --}}
        <div class="flex-1 min-w-0 flex flex-col">
            @php
                $subtitle = '';
                $pageTitle = '';
                $headerNotificationCount = 0;
                $headerNotificationUrl = '#';

                if ($user->role === 'admin') {
                    $subtitle  = 'Selamat Datang Kembali, ' . $user->name;
                    $pageTitle = 'Dashboard Admin';

                    if (str_starts_with($routeName, 'admin.pengguna')) {
                        $pageTitle = 'User Management';
                    } elseif (str_starts_with($routeName, 'admin.edukasi') || $routeName === 'modulC.index') {
                        $pageTitle = 'Content Management';
                    } elseif ($routeName === 'modulB.index') {
                        $pageTitle = 'Produk';
                    } elseif ($routeName === 'modulD.index') {
                        $pageTitle = 'Tentang Sistem';
                    } elseif (str_starts_with($routeName, 'admin.orders.')) {
                        $pageTitle = 'Data Pesanan';
                    } elseif (str_starts_with($routeName, 'ads.')) {
                        $pageTitle = 'Iklan / Promosi';
                    }

                    $headerNotificationCount = \App\Models\Order::whereIn('status_order', [
                        'menunggu_konfirmasi',
                        'pending_cancel',
                        'siap_dikirim',
                        'sendiri',
                    ])->count();

                    $headerNotificationUrl = route('admin.notifications.index');
                } else {
                    $subtitle  = 'Halo, ' . $user->name;
                    $pageTitle = 'Dashboard Mitra';

                    if (str_starts_with($routeName, 'mitra.products.')) {
                        $pageTitle = 'Kelola Produk';
                    } elseif (str_starts_with($routeName, 'mitra.orders.')) {
                        $pageTitle = 'Daftar Pesanan';
                    } elseif (str_starts_with($routeName, 'mitra.profile.')) {
                        $pageTitle = 'Pengaturan Toko';
                    } elseif (str_starts_with($routeName, 'mitra.sales.')) {
                        $pageTitle = 'Laporan Penjualan';
                    } elseif (request()->is('iklan*') || str_starts_with($routeName, 'ads.')) {
                        $pageTitle = 'Iklan / Promosi';
                    }

                    $headerNotificationCount = \App\Models\Order::where('mitra_id', $user->mitra_id ?? null)
                        ->where('status_order', 'menunggu_konfirmasi')
                        ->count();

                    $headerNotificationUrl = route('mitra.orders.index');
                }

                $headerNotificationLabel = $headerNotificationCount > 9 ? '9+' : $headerNotificationCount;
            @endphp

            <header class="h-20 bg-gradient-to-r from-emerald-700 to-emerald-500 text-white flex items-center justify-between px-8 shadow">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-100">{{ $subtitle }}</p>
                    <h1 class="text-2xl font-semibold leading-tight">{{ $pageTitle }}</h1>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Bell notifikasi --}}
                    <a href="{{ $headerNotificationUrl }}"
                       class="relative w-9 h-9 rounded-full border border-emerald-300 bg-emerald-600/40 flex items-center justify-center hover:bg-emerald-500/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                        </svg>

                        @if($headerNotificationCount > 0)
                            <span class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-[10px] font-semibold text-white flex items-center justify-center">
                                {{ $headerNotificationLabel }}
                            </span>
                        @endif
                    </a>

                    {{-- Kartu profil kanan --}}
<div class="flex items-center gap-3 bg-emerald-800 px-4 py-2 rounded-full">
    <div class="w-9 h-9 rounded-full bg-emerald-200 text-emerald-900 flex items-center justify-center font-semibold">
        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
    </div>
    <div class="text-xs leading-tight">
        <p class="font-semibold text-white">{{ $user->name }}</p>
        <p class="text-emerald-50">{{ $user->email }}</p>
    </div>
</div>
                </div>
            </header>

            <main class="flex-1 min-w-0 bg-emerald-50/60 p-6">
                @isset($header)
                    <div class="mb-4">{{ $header }}</div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </div>

{{-- =========================================================
   LAYOUT DEFAULT (PEMBELI / USER BIASA)
   ========================================================== --}}
@else
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>
@endif

</body>
</html>