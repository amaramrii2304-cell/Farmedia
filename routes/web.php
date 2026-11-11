<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserBerandaController;
use App\Http\Controllers\AdminEdukasiController;
use App\Http\Controllers\UserEdukasiController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MitraCommissionPaymentController;
use App\Http\Controllers\MitraOrderController;
use App\Http\Controllers\ShopProfileController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\AdminCommissionInvoiceController;
use App\Http\Controllers\MitraReviewController;
use App\Http\Controllers\AdminAdSubmissionController;
use App\Http\Controllers\MitraAdSubmissionController;
use App\Http\Controllers\Admin\AdminOrderPaymentController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\MitraProfileController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Mitra;
use App\Models\Product;
use App\Models\Edukasi;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Semua route yang butuh auth & email verified
Route::middleware(['auth', 'verified'])->group(function () {

    // ================== USER BIASA (PETANI) ==================
    Route::get('/user/beranda', [UserBerandaController::class, 'index'])->name('user.beranda');

    Route::get('/keranjang', [CartController::class, 'index'])->name('user.cart.index');
    Route::post('/keranjang/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/keranjang/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

    // Redirect otomatis setelah login sesuai role
    Route::get('/home', function () {
        $user = Auth::user();

        if ($user->role === 'mitra') {
            return redirect()->route('mitra.dashboard');
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.beranda');
    })->name('home');

    Route::get('/user/dashboard', fn () => view('user.dashboard'))->name('user.dashboard');
    Route::get('/user/data-saya', fn () => view('user.data'))->name('user.data');
    Route::get('/user/data-publik', fn () => view('user.public'))->name('user.public');

    Route::post('/produk/{product}/review', [ProductReviewController::class, 'store'])
        ->name('products.review.store');

    Route::get('/toko/{mitra}', [ShopProfileController::class, 'show'])
        ->name('toko.show');

    // ================== NOTIFIKASI USER ==================
    Route::get('/user/notifikasi', [UserNotificationController::class, 'index'])
        ->name('user.notifications.index');

    // ================== PESANAN SAYA (USER) ==================
    Route::get('/pesanan-saya', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/next-step', [OrderController::class, 'nextStep'])->name('orders.next');

    // ✅ TAMBAHAN (WAJIB): upload bukti transfer harus di dalam auth+verified group
    Route::post('/orders/{order}/upload-proof', [\App\Http\Controllers\OrderPaymentController::class, 'uploadProof'])
        ->name('orders.uploadProof');

    // ================== CHECKOUT (USER) ==================
    Route::match(['get', 'post'], '/checkout/from-cart', [CheckoutController::class, 'checkoutFromCart'])
        ->name('checkout.fromCart');

    Route::post('/checkout/cart/process', [CheckoutController::class, 'processCart'])
        ->name('checkout.cart.process');

    Route::get('/checkout/{product}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{product}', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/request-cancel', [OrderController::class, 'requestCancel'])->name('orders.request-cancel');

    Route::post('/user/update-address', function () {
        $user = Auth::user();
        $user->alamat_lengkap = request('alamat_lengkap');
        $user->save();
        return back()->with('success', 'Alamat berhasil diperbarui.');
    })->name('user.updateAddress');

    // ================== EDUKASI USER ==================
    Route::get('/user/edukasi', [UserEdukasiController::class, 'index'])->name('user.edukasi.index');
    Route::get('/user/edukasi/{edukasi}', [UserEdukasiController::class, 'show'])->name('user.edukasi.show');
    Route::get('/user/edukasi/ajax-search', [UserEdukasiController::class, 'ajaxSearch'])->name('user.edukasi.ajax');

    // Detail produk user
    Route::get('/user/produk/{product}', [UserBerandaController::class, 'show'])->name('user.produk.show');
    Route::post('/user/produk/{product}/review', [ProductReviewController::class, 'store'])->name('user.reviews.store');

    // ================== MITRA ==================
    Route::middleware(['is_mitra'])
        ->prefix('mitra')
        ->name('mitra.')
        ->group(function () {

            Route::get('/dashboard', [\App\Http\Controllers\MitraDashboardController::class, 'index'])
                ->name('dashboard');

            // ✅ IKLAN MITRA
            Route::prefix('iklan')->name('ads.')->group(function () {
                Route::get('/', [MitraAdSubmissionController::class, 'index'])->name('index');
                Route::get('/buat', [MitraAdSubmissionController::class, 'create'])->name('create');
                Route::post('/', [MitraAdSubmissionController::class, 'store'])->name('store');
                Route::get('/{ad}', [MitraAdSubmissionController::class, 'show'])->name('show');

                Route::post('/{ad}/upload-bukti', [MitraAdSubmissionController::class, 'uploadProof'])->name('uploadProof');
                Route::post('/{ad}/batalkan', [MitraAdSubmissionController::class, 'cancel'])->name('cancel');
            });

            // Produk mitra
            Route::resource('/produk', ProductController::class)
                ->parameters(['produk' => 'product'])
                ->names([
                    'index'   => 'products.index',
                    'create'  => 'products.create',
                    'store'   => 'products.store',
                    'show'    => 'products.show',
                    'edit'    => 'products.edit',
                    'update'  => 'products.update',
                    'destroy' => 'products.destroy',
                ]);

            // Pesanan mitra
            Route::get('/daftar-pesanan', [MitraOrderController::class, 'index'])->name('orders.index');
            Route::post('/daftar-pesanan/{order}/konfirmasi', [MitraOrderController::class, 'confirm'])->name('orders.confirm');
            Route::post('/daftar-pesanan/{order}/tolak', [MitraOrderController::class, 'reject'])->name('orders.reject');

            Route::get('/orders/{order}', [MitraOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/next-step', [MitraOrderController::class, 'nextStep'])->name('orders.next');

            Route::post('/orders/{order}/pack', [MitraOrderController::class, 'pack'])->name('orders.pack');
            Route::post('/orders/{order}/ship', [MitraOrderController::class, 'ship'])->name('orders.ship');
            Route::post('/orders/{order}/ready-pickup', [MitraOrderController::class, 'readyPickup'])->name('orders.readyPickup');
            Route::post('/orders/{order}/finish', [MitraOrderController::class, 'finish'])->name('orders.finish');

            Route::post('/orders/{order}/confirm-payment', [MitraOrderController::class, 'confirmPayment'])->name('orders.confirmPayment');
            Route::post('/orders/{order}/ready-ship', [MitraOrderController::class, 'readyShip'])->name('orders.readyShip');

            Route::get('/orders/{order}/print-resi', [MitraOrderController::class, 'printResi'])
                ->name('orders.printResi');

            Route::get('/ulasan-produk', [MitraReviewController::class, 'index'])
                ->name('reviews.index');

            Route::get('/komisi/bayar', [MitraCommissionPaymentController::class, 'payForm'])
                ->name('commission.pay');

            Route::post('/komisi/bayar', [MitraCommissionPaymentController::class, 'submitPayment'])
                ->name('commission.pay.submit');

            // Laporan & komisi mitra
            Route::get('/laporan-penjualan', [\App\Http\Controllers\MitraSalesReportController::class, 'index'])
                ->name('sales.index');

            Route::get('/komisi', [\App\Http\Controllers\MitraCommissionController::class, 'index'])
                ->name('commission.index');

            // Cancel approve/reject
            Route::post('/orders/{order}/cancel/approve', [MitraOrderController::class, 'approveCancel'])->name('orders.cancel.approve');
            Route::post('/orders/{order}/cancel/reject', [MitraOrderController::class, 'rejectCancel'])->name('orders.cancel.reject');

            // Profil toko
            Route::get('/profil-toko', [MitraProfileController::class, 'edit'])->name('profile.edit');
            Route::post('/profil-toko', [MitraProfileController::class, 'update'])->name('profile.update');
        });

    // ================== ADMIN ==================
    Route::middleware(['is_admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Dashboard admin
            Route::get('/dashboard', function () {
                $jumlahUser     = User::count();
                $jumlahMitra    = Mitra::count();
                $jumlahProduk   = Product::count();
                $jumlahEdukasi  = Edukasi::count();

                return view('admin.dashboard', compact('jumlahUser','jumlahMitra','jumlahProduk','jumlahEdukasi'));
            })->name('dashboard');

            // ✅ IKLAN ADMIN
            Route::prefix('iklan')->name('ads.')->group(function () {
                Route::get('/', [AdminAdSubmissionController::class, 'index'])->name('index');
                Route::get('/{ad}', [AdminAdSubmissionController::class, 'show'])->name('show');

                Route::post('/{ad}/approve', [AdminAdSubmissionController::class, 'approve'])->name('approve');
                Route::post('/{ad}/reject', [AdminAdSubmissionController::class, 'reject'])->name('reject');
                Route::post('/{ad}/mark-paid', [AdminAdSubmissionController::class, 'markPaid'])->name('markPaid');
                Route::post('/{ad}/activate', [AdminAdSubmissionController::class, 'activate'])->name('activate');
                Route::post('/{ad}/end', [AdminAdSubmissionController::class, 'end'])->name('end');
            });

            Route::get('/commission-invoices', [AdminCommissionInvoiceController::class, 'index'])
                ->name('commission_invoices.index');

            Route::get('/commission-invoices/{invoice}', [AdminCommissionInvoiceController::class, 'show'])
                ->name('commission_invoices.show');

            Route::post('/commission-invoices/{invoice}/approve', [AdminCommissionInvoiceController::class, 'approve'])
                ->name('commission_invoices.approve');

            Route::post('/commission-invoices/{invoice}/reject', [AdminCommissionInvoiceController::class, 'reject'])
                ->name('commission_invoices.reject');

            Route::prefix('transaksi-cod')->name('cod.')->group(function () {
                Route::get('/', [\App\Http\Controllers\AdminCodController::class, 'index'])->name('index');
                Route::post('/{order}/receive', [\App\Http\Controllers\AdminCodController::class, 'receive'])->name('receive');
                Route::post('/{order}/deduct', [\App\Http\Controllers\AdminCodController::class, 'deduct'])->name('deduct');
                Route::post('/{order}/pay', [\App\Http\Controllers\AdminCodController::class, 'pay'])->name('pay');
            });

            Route::get('/komisi', [\App\Http\Controllers\AdminCommissionController::class, 'index'])
                ->name('commissions.index');

            Route::get('/notifikasi', [AdminNotificationController::class, 'index'])
                ->name('notifications.index');

            Route::get('/notifikasi/count', [AdminNotificationController::class, 'count'])
                ->name('notifications.count');

            // CRUD produk admin
            Route::resource('/products', ProductController::class)->names('products');

            // CRUD pengguna
            Route::resource('/pengguna', UserController::class)->names('pengguna');

            // CRUD edukasi
            Route::resource('/edukasi', AdminEdukasiController::class)->names('edukasi');

            // Pesanan admin
            Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/pesanan/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::post('/pesanan/{order}/finish', [AdminOrderController::class, 'finish'])->name('orders.finish');
            Route::post('/pesanan/{order}/ship', [AdminOrderController::class, 'ship'])->name('orders.ship');
            Route::post('/pesanan/{order}/approve-transfer', [AdminOrderController::class, 'approveTransfer'])->name('orders.approveTransfer');

            // route lihat bukti transfer (anti 403)
            Route::get('/pesanan/{order}/transfer-proof', [AdminOrderController::class, 'transferProof'])
                ->name('orders.transferProof');

            Route::get('/pesanan/{order}/print-resi', [AdminOrderController::class, 'printResi'])->name('orders.printResi');

            // Modul
            Route::get('/modul-a', fn() => view('modulA.index'))->name('modulA.index');
            Route::get('/modul-b', [ProductController::class, 'index'])->name('modulB.index');
            Route::get('/modul-c', [AdminEdukasiController::class, 'index'])->name('modulC.index');
            Route::get('/modul-d', fn() => view('modulD.index'))->name('modulD.index');
        });

});

// Profil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
