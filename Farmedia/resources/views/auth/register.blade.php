<x-guest-layout>
    <div class="mb-6 text-center">
        <div class="inline-flex items-center px-3 py-1 mb-3 text-[11px] font-medium rounded-full bg-[#E6F8EC] text-[#0F6D3F] border border-[#C2E9CF]">
            🌱 Farmedia • Daftar Akun
        </div>
        <h1 class="text-2xl font-semibold text-[#103319] mb-1">
            Buat Akun Farmedia
        </h1>
        <p class="text-sm text-[#4D6B55]">
            Bergabung sebagai petani, pembeli, atau mitra penjual untuk ekosistem pertanian digital.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-[#355743] text-xs font-medium" />
            <x-text-input
                id="name"
                class="block mt-1 w-full border-[#D8E9DD] focus:border-[#0F6D3F] focus:ring-[#38A169] rounded-md text-sm"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-xs" />
        </div>

        {{-- Email Address --}}
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-[#355743] text-xs font-medium" />
            <x-text-input
                id="email"
                class="block mt-1 w-full border-[#D8E9DD] focus:border-[#0F6D3F] focus:ring-[#38A169] rounded-md text-sm"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs" />
        </div>

        {{-- Role --}}
        <div>
            <x-input-label for="role" :value="__('Daftar sebagai')" class="text-[#355743] text-xs font-medium" />

            <select
                id="role"
                name="role"
                class="block mt-1 w-full rounded-md border-[#D8E9DD] bg-white text-sm text-[#103319] shadow-sm focus:border-[#0F6D3F] focus:ring-[#38A169]"
            >
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Petani / Pembeli</option>
                <option value="mitra" {{ old('role') === 'mitra' ? 'selected' : '' }}>Mitra (Penjual)</option>
            </select>

            <x-input-error :messages="$errors->get('role')" class="mt-2 text-xs" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-[#355743] text-xs font-medium" />

            <x-text-input
                id="password"
                class="block mt-1 w-full border-[#D8E9DD] focus:border-[#0F6D3F] focus:ring-[#38A169] rounded-md text-sm"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="text-[#355743] text-xs font-medium" />

            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full border-[#D8E9DD] focus:border-[#0F6D3F] focus:ring-[#38A169] rounded-md text-sm"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-xs" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a
                class="underline text-xs text-[#4D6B55] hover:text-[#15803D] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#38A169]"
                href="{{ route('login') }}"
            >
                {{ __('Sudah punya akun? Masuk') }}
            </a>

            <x-primary-button
                class="ms-4 bg-[#0F6D3F] hover:bg-[#0B5A34] border-0 focus:ring-[#38A169] text-sm px-5 py-2"
            >
                {{ __('Daftar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
