<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\EdukasiApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\MitraDashboardController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\AdApiController;
use App\Http\Controllers\Api\MitraReviewApiController;
use App\Http\Controllers\Api\MitraCommissionApiController;
use App\Http\Controllers\Api\MitraCommissionPaymentApiController;
use App\Http\Controllers\Api\MitraAdApiController;
use App\Http\Controllers\Api\MitraOrderApiController;
use App\Http\Controllers\Api\MitraSettingsApiController;
use App\Http\Controllers\Api\OrderPaymentApiController;
use App\Http\Controllers\Api\MitraSalesReportApiController;
use App\Http\Controllers\Api\AdminOrderApiController;

Route::get('/ping', function () {
    return response()->json(['ok' => true]);
});

// =======================
// AUTH PUBLIC
// =======================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});

// =======================
// PRODUCTS & PUBLIC DATA
// =======================
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{product}', [ProductApiController::class, 'show']);
Route::get('/products/{product}/reviews', [ProductApiController::class, 'reviews']);
Route::get('/ads', [AdApiController::class, 'index']);
Route::get('/mitra/{mitraId}/products', [ProductApiController::class, 'productsByMitraPublic'])->whereNumber('mitraId');
Route::get('/mitra/{mitraId}', [ProductApiController::class, 'mitraDetailPublic'])->whereNumber('mitraId');
Route::get('/edukasi', [EdukasiApiController::class, 'index']);
Route::get('/edukasi/{id}', [EdukasiApiController::class, 'show']);

// =======================
// PROTECTED (auth:sanctum)
// =======================
Route::middleware('auth:sanctum')->group(function () {

    // ME & PROFILE
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::put('/me/password', [AuthController::class, 'updatePassword']);
    Route::post('/me/avatar', [AuthController::class, 'updateAvatar']);
    Route::delete('/me', [AuthController::class, 'deleteAccount']);

    // BUYER PAYMENT
    Route::post('/orders/{order}/submit-transfer-proof', [OrderApiController::class, 'submitTransferProof']);

    // ADMIN VERIFICATION
    Route::prefix('admin')->group(function () {
        Route::post('/orders/{order}/verify-transfer-payment', [AdminOrderApiController::class, 'verifyTransferPayment']);
        Route::post('/orders/{order}/complete', [AdminOrderApiController::class, 'markCompleted']);
        Route::get('/orders/{order}/resi', [AdminOrderApiController::class, 'printResi']);
    });

    // MITRA DASHBOARD & REPORTS
    Route::get('/mitra/dashboard-stats', [MitraDashboardController::class, 'index']);
    Route::get('/mitra/sales-report', [MitraSalesReportApiController::class, 'index']);
    Route::get('/mitra/commission', [MitraCommissionApiController::class, 'index']);

    // =======================
    // ✅ DAFTAR PESANAN MITRA (FIXED)
    // =======================
    Route::get('/mitra/orders', [MitraOrderApiController::class, 'index']);
    Route::get('/mitra/orders/unread-count', [MitraOrderApiController::class, 'unreadCount']);
    Route::get('/mitra/orders/{order}', [MitraOrderApiController::class, 'show']);

    Route::post('/mitra/orders/{order}/confirm', [MitraOrderApiController::class, 'confirm']);
    Route::post('/mitra/orders/{order}/reject', [MitraOrderApiController::class, 'reject']);
    Route::post('/mitra/orders/{order}/process', [MitraOrderApiController::class, 'process']);
    Route::post('/mitra/orders/{order}/pack', [MitraOrderApiController::class, 'pack']);

    // Delivery Flow
    Route::post('/mitra/orders/{order}/ready-ship', [MitraOrderApiController::class, 'readyShip']); 
    Route::post('/mitra/orders/{order}/ship', [MitraOrderApiController::class, 'markShipped']); 

    // Pickup Flow
    Route::post('/mitra/orders/{order}/ready-pickup', [MitraOrderApiController::class, 'readyPickup']); 
    Route::post('/mitra/orders/{order}/complete-pickup', [MitraOrderApiController::class, 'completePickup']); 
    Route::post('/mitra/orders/{order}/complete', [MitraOrderApiController::class, 'completePickup']); // Fallback

    Route::get('/mitra/orders/{order}/resi', [MitraOrderApiController::class, 'printResi']);

    // =======================
    // KOMISI MITRA
    // =======================
    Route::get('/mitra/commission/invoice', [MitraCommissionPaymentApiController::class, 'showActiveInvoice']);
    Route::get('/mitra/commission/invoice/active', [MitraCommissionPaymentApiController::class, 'showActiveInvoice']);
    Route::post('/mitra/commission/invoice/{id}/submit-proof', [MitraCommissionPaymentApiController::class, 'submitProof'])->whereNumber('id');

    // PRODUK MITRA
    Route::get('/mitra/products', [ProductApiController::class, 'mitraIndex']);
    Route::post('/mitra/products', [ProductApiController::class, 'mitraStore']);
    Route::get('/mitra/products/{id}', [ProductApiController::class, 'mitraShow']);
    Route::put('/mitra/products/{id}', [ProductApiController::class, 'mitraUpdate']);
    Route::post('/mitra/products/{id}', [ProductApiController::class, 'mitraUpdate']);
    Route::delete('/mitra/products/{id}', [ProductApiController::class, 'mitraDestroy']);

    // BUYER ORDERS
    Route::post('/orders', [OrderApiController::class, 'store']);
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/orders/{order}', [OrderApiController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderApiController::class, 'requestCancel']);
    Route::post('/orders/{order}/review', [OrderApiController::class, 'submitReview']);
    Route::post('/orders/{order}/received', [OrderApiController::class, 'confirmReceived']);

    // NOTIFICATIONS & CART
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add/{product}', [CartApiController::class, 'add']);
    Route::put('/cart/{product}', [CartApiController::class, 'update']);
    Route::delete('/cart/{product}', [CartApiController::class, 'remove']);
    Route::delete('/cart/clear', [CartApiController::class, 'clear']);
    Route::get('/mitra/reviews', [MitraReviewApiController::class, 'index']);

    // IKLAN MITRA
    Route::prefix('mitra')->group(function () {
        Route::get('/ads', [MitraAdApiController::class, 'index']);
        Route::post('/ads', [MitraAdApiController::class, 'store']);
        Route::get('/ads/{ad}', [MitraAdApiController::class, 'show']);
        Route::post('/ads/{ad}/cancel', [MitraAdApiController::class, 'cancel']);
        Route::post('/ads/{ad}/upload-proof', [MitraAdApiController::class, 'uploadProof']);
    });

    // SETTINGS MITRA
    Route::get('/mitra/settings', [MitraSettingsApiController::class, 'show']);
    Route::put('/mitra/settings/account', [MitraSettingsApiController::class, 'updateAccount']);
    Route::put('/mitra/settings/shipping', [MitraSettingsApiController::class, 'updateShipping']);
    Route::put('/mitra/settings/payment', [MitraSettingsApiController::class, 'updatePayment']);
    Route::put('/mitra/settings/holiday', [MitraSettingsApiController::class, 'updateHoliday']);
    Route::put('/mitra/settings/store', [MitraSettingsApiController::class, 'updateStore']);
    Route::post('/mitra/settings/logo', [MitraSettingsApiController::class, 'uploadLogo']);
});