<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center">
        <div class="inline-flex items-center px-3 py-1 mb-3 text-[11px] font-medium rounded-full bg-[#E6F8EC] text-[#0F6D3F] border border-[#C2E9CF]">
            🌾 Farmedia • Masuk Akun
        </div>
        <h1 class="text-2xl font-semibold text-[#103319] mb-1">
            Selamat Datang Kembali
        </h1>
        <p class="text-sm text-[#4D6B55]">
            Masuk untuk mengelola pesanan, hasil tani, dan aktivitas pertanian Anda.
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <x-input-label
                for="email"
                :value="__('Email')"
                class="text-[#355743] text-xs font-medium"
            />
            <x-text-input
                id="email"
                class="block mt-1 w-full border-[#D8E9DD] focus:border-[#0F6D3F] focus:ring-[#38A169] rounded-md text-sm"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label
                for="password"
                :value="__('Password')"
                class="text-[#355743] text-xs font-medium"
            />
            <x-text-input
                id="password"
                class="block mt-1 w-full border-[#D8E9DD] focus:border-[#0F6D3F] focus:ring-[#38A169] rounded-md text-sm"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center justify-between mt-2">
            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-[#C7DACD] text-[#15803D] shadow-sm focus:ring-[#38A169]"
                    name="remember"
                >
                <span class="ms-2 text-xs text-[#4D6B55]">
                    {{ __('Ingat saya') }}
                </span>
            </label>

            @if (Route::has('password.request'))
                <a
                    class="underline text-xs text-[#4D6B55] hover:text-[#15803D] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#38A169]"
                    href="{{ route('password.request') }}"
                >
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-between mt-6">
            <a
                href="{{ route('register') }}"
                class="text-xs text-[#4D6B55] hover:text-[#15803D] underline underline-offset-4"
            >
                {{ __('Belum punya akun? Daftar') }}
            </a>

            <x-primary-button
                class="ms-3 bg-[#0F6D3F] hover:bg-[#0B5A34] border-0 focus:ring-[#38A169] text-sm px-5 py-2"
            >
                {{ __('Masuk') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
