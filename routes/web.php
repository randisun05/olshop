<?php

use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\ComplaintController as CustomerComplaintController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Public\MidtransNotificationController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CouponController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\OrderController as StorefrontOrderController;
use App\Http\Controllers\Storefront\PageController as StorefrontPageController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/produk', [StorefrontProductController::class, 'index'])->name('catalog');
Route::get('/produk/{slug}', [StorefrontProductController::class, 'show'])->name('catalog.show');
Route::get('/halaman/{slug}', [StorefrontPageController::class, 'show'])->name('page.show');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
Route::put('/keranjang/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::post('/checkout/kupon', [CouponController::class, 'check'])->middleware('throttle:20,1')->name('checkout.coupon.check');

Route::get('/pesanan/lacak', [StorefrontOrderController::class, 'lookupForm'])->name('order.lookup');
Route::post('/pesanan/lacak', [StorefrontOrderController::class, 'lookup'])->name('order.lookup.submit');
Route::get('/pesanan/{order}', [StorefrontOrderController::class, 'show'])->name('order.show');
Route::post('/pesanan/{order}/bukti-bayar', [StorefrontOrderController::class, 'uploadProof'])->name('order.proof');
Route::get('/pesanan/{order}/invoice', [StorefrontOrderController::class, 'invoice'])->name('order.invoice');

Route::post('/webhook/midtrans', MidtransNotificationController::class)->middleware('throttle:60,1')->name('webhook.midtrans');

Route::middleware('guest')->prefix('auth')->name('social.')->group(function () {
    Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('redirect');
    Route::get('/{provider}/callback', [SocialAuthController::class, 'callback'])->name('callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return request()->user()->hasAnyRole(['Super Admin', 'Admin', 'Staff Gudang', 'Staff CS'])
            ? redirect()->route('admin.dashboard')
            : redirect()->route('customer.dashboard');
    })->name('dashboard');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->middleware('throttle:30,1')->name('wishlist.toggle');

    Route::prefix('akun-saya')->name('account.')->group(function () {
        Route::get('/keamanan', [SecurityController::class, 'edit'])->name('security.edit');
    });
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:Super Admin|Admin|Staff Gudang|Staff CS', 'two_factor.required'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::middleware('permission:categories.manage')->group(function () {
            Route::resource('categories', CategoryController::class)->except('show');
        });

        Route::middleware('permission:brands.manage')->group(function () {
            Route::resource('brands', BrandController::class)->except('show');
        });

        Route::middleware('permission:attributes.manage')->group(function () {
            Route::resource('attributes', AttributeController::class)->except('show');
        });

        Route::middleware('permission:products.manage')->group(function () {
            Route::resource('products', AdminProductController::class)->except('show');
        });

        Route::middleware('permission:shipping.manage')->group(function () {
            Route::resource('shipping-zones', ShippingZoneController::class)->except('show', 'create', 'edit');
        });

        Route::middleware('permission:coupons.manage')->group(function () {
            Route::resource('coupons', AdminCouponController::class)->except('show');
        });

        Route::middleware('permission:reviews.manage')->group(function () {
            Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
            Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        });

        Route::middleware('permission:banners.manage')->group(function () {
            Route::resource('banners', BannerController::class)->except('show');
        });

        Route::middleware('permission:pages.manage')->group(function () {
            Route::resource('pages', AdminPageController::class)->except('show');
        });

        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('/pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('permission:reports.view')->prefix('laporan')->name('reports.')->group(function () {
            Route::get('/penjualan', [ReportController::class, 'sales'])->name('sales');
            Route::get('/penjualan/export/excel', [ReportController::class, 'salesExportExcel'])->name('sales.export.excel');
            Route::get('/penjualan/export/pdf', [ReportController::class, 'salesExportPdf'])->name('sales.export.pdf');

            Route::get('/produk-terlaris', [ReportController::class, 'topProducts'])->name('top-products');
            Route::get('/produk-terlaris/export/excel', [ReportController::class, 'topProductsExportExcel'])->name('top-products.export.excel');

            Route::get('/stok', [ReportController::class, 'stock'])->name('stock');
            Route::get('/stok/export/excel', [ReportController::class, 'stockExportExcel'])->name('stock.export.excel');
        });

        Route::middleware('permission:orders.manage')->group(function () {
            Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
            Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
            Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');

            Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/pesanan/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::post('/pesanan/{order}/proses', [AdminOrderController::class, 'markProcessing'])->name('orders.process');
            Route::post('/pesanan/{order}/kirim', [AdminOrderController::class, 'markShipped'])->name('orders.ship');
            Route::post('/pesanan/{order}/selesai', [AdminOrderController::class, 'markCompleted'])->name('orders.complete');
            Route::post('/pesanan/{order}/batal', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        });

        Route::middleware('role:Super Admin')->group(function () {
            Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        });

        Route::middleware('permission:complaints.manage')->group(function () {
            Route::get('/komplain', [AdminComplaintController::class, 'index'])->name('complaints.index');
            Route::get('/komplain/{complaint}', [AdminComplaintController::class, 'show'])->name('complaints.show');
            Route::post('/komplain/{complaint}/respon', [AdminComplaintController::class, 'respond'])->name('complaints.respond');
        });
    });

Route::prefix('akun')
    ->name('customer.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
        Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::post('/pesanan/{order}/terima', [CustomerOrderController::class, 'confirmReceived'])->name('orders.confirm');
        Route::post('/pesanan-item/{orderItem}/ulasan', [CustomerReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');

        Route::get('/komplain', [CustomerComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/pesanan/{order}/komplain', [CustomerComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/pesanan/{order}/komplain', [CustomerComplaintController::class, 'store'])->middleware('throttle:5,1')->name('complaints.store');

        Route::get('/alamat', [AddressController::class, 'index'])->name('addresses.index');
        Route::post('/alamat', [AddressController::class, 'store'])->name('addresses.store');
        Route::put('/alamat/{address}', [AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/alamat/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    });
