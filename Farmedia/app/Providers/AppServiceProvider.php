<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL; // 1. Tambahan aman: Import class URL Laravel
use App\Models\CartItem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 2. Tambahan aman: Paksa HTTPS hanya jika diakses melalui terowongan Ngrok
        if (str_contains(request()->url(), 'ngrok-free.dev') || str_contains(request()->url(), 'ngrok-free.app')) {
            URL::forceScheme('https');
        }

        // ✅ Badge keranjang global (total qty dari DB cart_items) - Tetap utuh & aman
        View::composer('*', function ($view) {
            $cartCount = 0;

            if (Auth::check()) {
                $cartCount = (int) CartItem::where('user_id', Auth::id())
                    ->sum('qty'); // total qty
            }

            $view->with('cartCount', $cartCount);
        });
    }
}