<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Farmedia') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            {{-- fallback tailwind inline (bawaan) --}}
            <style>
                /* biarkan isi style fallback-mu di sini */
            </style>
        @endif
    </head>
    {{-- nuansa hijau lembut di background --}}
    <body class="bg-[#F3FFF7] text-[#103319] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 border border-[#96C9A3] hover:border-[#0F6D3F] rounded-sm text-sm leading-normal text-[#103319]"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 text-[#103319] border border-transparent hover:border-[#96C9A3] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 border border-[#96C9A3] hover:border-[#0F6D3F] rounded-sm text-sm leading-normal text-[#103319]">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-700 lg:grow">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row bg-white rounded-2xl shadow-[0_0_1px_rgba(0,0,0,0.04),0_14px_40px_rgba(15,109,63,0.08)] overflow-hidden border border-[#E2F3E6]">

                {{-- KIRI: FORM LOGIN --}}
                <div class="text-[13px] leading-[20px] flex-1 p-6 pb-10 lg:p-10 bg-white">
                    {{-- badge kecil tema pertanian --}}
                    <div class="inline-flex items-center px-3 py-1 mb-3 text-[11px] font-medium rounded-full bg-[#E6F8EC] text-[#0F6D3F] border border-[#C2E9CF]">
                        🌱 Farmedia • Mitra Tani Digital
                    </div>

                    <h1 class="mb-3 text-3xl font-semibold text-[#103319]">
                        Selamat Datang di Farmedia 🧑‍🌾
                    </h1>
                    <p class="mb-6 text-[#4D6B55] text-sm lg:text-base">
                        Ciptakan petani terintegrasi dan hasil tani yang berkualitas melalui solusi digital yang mudah digunakan.
                    </p>
                    
                    <h2 class="mb-3 text-lg font-medium text-[#103319]">
                        Masuk ke Akun Anda
                    </h2>

                    {{-- FORM LOGIN --}}
                    <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
                        @csrf

                        <div>
                            <label class="block mb-1 text-xs font-medium text-[#355743]">
                                Email
                            </label>
                            <input
                                type="email"
                                name="email"
                                placeholder="contoh: petani@farmedia.com"
                                value="{{ old('email') }}"
                                required
                                class="w-full p-3 border border-[#D8E9DD] rounded-md bg-white text-sm focus:outline-none focus:border-[#0F6D3F] focus:ring-1 focus:ring-[#38A169]"
                            >
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-xs font-medium text-[#355743]">
                                Password
                            </label>
                            <input
                                type="password"
                                name="password"
                                placeholder="Masukkan password Anda"
                                required
                                class="w-full p-3 border border-[#D8E9DD] rounded-md bg-white text-sm focus:outline-none focus:border-[#0F6D3F] focus:ring-1 focus:ring-[#38A169]"
                            >
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="mt-1 px-5 py-2.5 font-medium text-sm text-white bg-[#0F6D3F] rounded-md hover:bg-[#0B5A34] transition-all shadow-[0_8px_20px_rgba(15,109,63,0.25)]">
                            Login
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-[#4D6B55]">
                            Belum punya akun? 
                            <a href="{{ route('register') }}" class="font-semibold underline underline-offset-4 text-[#15803D] ml-1">
                                Daftar Sekarang
                            </a>
                        </p>
                    </div>
                </div>

                {{-- KANAN: GAMBAR --}}
                <div class="bg-[#F6FFF9] w-full lg:w-[420px] shrink-0 flex items-center justify-center border-t lg:border-t-0 lg:border-l border-[#E2F3E6] relative">
                    <div class="absolute inset-0 pointer-events-none opacity-60" style="background-image: radial-gradient(circle at 0 0, #D0F3D9 0, transparent 55%), radial-gradient(circle at 100% 100%, #B7E8C7 0, transparent 55%);"></div>
                    <div class="relative w-full h-full flex items-center justify-center p-6 lg:p-8">
                        <img 
                            src="{{ asset('images/farmedia.png') }}" 
                            alt="Aplikasi Farmedia untuk Petani" 
                            class="max-w-full max-h-[320px] lg:max-h-[380px] object-contain drop-shadow-[0_16px_40px_rgba(15,109,63,0.18)]"
                        >
                    </div>
                </div>
            </main>
        </div>

        @if (Route::has('login'))
            <div class="h-14 hidden lg:block"></div>
        @endif
    </body>
</html>
