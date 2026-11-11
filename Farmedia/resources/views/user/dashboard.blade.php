<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan selamat datang --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-2">
                        Selamat Datang, {{ Auth::user()->name }}!
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Anda login sebagai <span class="font-semibold">{{ Auth::user()->role }}</span>.
                        Silakan gunakan menu di bawah untuk mengelola data Anda.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
