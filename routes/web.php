<?php

use App\Http\Controllers\InstallerController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\StaticPageController;

use App\Http\Controllers\Admin\SellerReviewController;
use App\Http\Controllers\Admin\CatalogManagementController;
use App\Http\Controllers\Admin\HomepageBannerController;
use App\Http\Controllers\Admin\HomepageShelfController;
use App\Http\Controllers\Admin\HomepageDesignRevisionController;
use App\Http\Controllers\Admin\HomepageMerchandisingController;
use App\Http\Controllers\Admin\SiteAppearanceController;
use App\Http\Controllers\Admin\StaticPageManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\SellerApplicationController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/setup', [InstallerController::class, 'show'])->name('installer.show');
Route::post('/setup', [InstallerController::class, 'install'])->middleware('throttle:5,5')->name('installer.install');

Route::middleware(['guest', SetLocale::class])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register/send-code', [RegisteredUserController::class, 'sendCode'])->middleware('throttle:3,10')->name('register.send-code');
    Route::get('/register/verify', [RegisteredUserController::class, 'showVerification'])->name('register.verify');
    Route::post('/register/verify-code', [RegisteredUserController::class, 'verifyCode'])->middleware('throttle:5,1')->name('register.verify-code');
    Route::post('/register/resend-code', [RegisteredUserController::class, 'resendCode'])->middleware('throttle:3,10')->name('register.resend-code');
    Route::get('/register/complete', [RegisteredUserController::class, 'complete'])->name('register.complete');
    Route::post('/register/complete', [RegisteredUserController::class, 'store'])->middleware('throttle:5,10')->name('register.store');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');


Route::post('/webhooks/payments/{provider}', PaymentWebhookController::class)
    ->middleware('throttle:120,1')->name('payments.webhook');

Route::middleware(SetLocale::class)->group(function () {
    Route::get('/', [StorefrontController::class, 'index'])->name('storefront.home');
    Route::get('/categories/{slug}', [StorefrontController::class, 'category'])
        ->where('slug', '[A-Za-z0-9_-]+')->name('categories.show');
    Route::get('/products/{publicId}', [StorefrontController::class, 'show'])
        ->whereUuid('publicId')->name('products.show');
    Route::get('/pages/{slug}', [StaticPageController::class, 'show'])
        ->where('slug', '[A-Za-z0-9_-]+')->name('pages.show');

    Route::post('/webhooks/shipping/{provider}', [\App\Http\Controllers\ShippingWebhookController::class, 'handle'])
        ->middleware('throttle:120,1')->name('shipping.webhook');

    Route::middleware('auth')->group(function () {
        Route::get('/seller/apply', [SellerApplicationController::class, 'create'])->name('seller.application.create');
        Route::post('/seller/apply', [SellerApplicationController::class, 'store'])->middleware('throttle:5,10')->name('seller.application.store');
        Route::get('/seller/applications/{seller}', [SellerApplicationController::class, 'show'])->whereNumber('seller')->name('seller.application.show');

        Route::prefix('seller/products')->name('seller.products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->middleware('throttle:10,1')->name('store');
            Route::patch('/{product}/media/{media}/alt', [ProductController::class, 'updateMediaAlt'])
                ->whereNumber(['product', 'media'])->middleware('throttle:30,1')->name('media.alt');
            Route::patch('/{product}/media/reorder', [ProductController::class, 'reorderMedia'])
                ->whereNumber('product')->middleware('throttle:20,1')->name('media.reorder');
            Route::patch('/{product}/media/{media}/primary', [ProductController::class, 'setPrimaryMedia'])
                ->whereNumber(['product', 'media'])->middleware('throttle:30,1')->name('media.primary');
            Route::delete('/{product}/media/{media}', [ProductController::class, 'destroyMedia'])
                ->whereNumber(['product', 'media'])->middleware('throttle:30,1')->name('media.destroy');
        });

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/items', [CartController::class, 'add'])->name('cart.items.add');
        Route::delete('/cart/items/{item}', [CartController::class, 'remove'])->name('cart.items.remove');
        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
        Route::get('/account', [AccountController::class, 'index'])->name('account.index');
        Route::get('/account/orders/{publicId}', [AccountController::class, 'showOrder'])->name('account.orders.show');
        Route::post('/orders/{publicId}/pay', [PaymentController::class, 'start'])->name('payments.start');

        Route::get('/orders/{publicId}/returns/create', [ReturnController::class, 'create'])->name('returns.create');
        Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
        Route::get('/returns/{publicId}', [ReturnController::class, 'show'])->name('returns.show');

        Route::prefix('admin')->name('admin.')->middleware('permission:admin.access')->group(function () {
            Route::get('/', DashboardController::class)->middleware('permission:admin.dashboard.view')->name('dashboard');
            Route::get('/users', [UserManagementController::class, 'index'])->middleware('permission:users.manage')->name('users.index');
            Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->middleware('permission:users.manage')->name('users.status');
            Route::get('/pages', [StaticPageManagementController::class, 'index'])->middleware('permission:pages.publish')->name('pages.index');
            Route::get('/pages/create', [StaticPageManagementController::class, 'create'])->middleware('permission:pages.publish')->name('pages.create');
            Route::post('/pages', [StaticPageManagementController::class, 'store'])->middleware('permission:pages.publish')->name('pages.store');
            Route::get('/pages/{page}/edit', [StaticPageManagementController::class, 'edit'])->whereNumber('page')->middleware('permission:pages.publish')->name('pages.edit');
            Route::put('/pages/{page}', [StaticPageManagementController::class, 'update'])->whereNumber('page')->middleware('permission:pages.publish')->name('pages.update');
            Route::delete('/pages/{page}', [StaticPageManagementController::class, 'destroy'])->whereNumber('page')->middleware('permission:pages.publish')->name('pages.destroy');
            Route::get('/appearance', [SiteAppearanceController::class, 'edit'])->middleware('permission:appearance.manage')->name('appearance.edit');
            Route::put('/appearance', [SiteAppearanceController::class, 'update'])->middleware('permission:appearance.manage')->name('appearance.update');
            Route::get('/homepage-merchandising', [HomepageMerchandisingController::class, 'index'])->middleware('permission:homepage.manage')->name('homepage-merchandising.index');
            Route::put('/homepage-merchandising', [HomepageMerchandisingController::class, 'update'])->middleware('permission:homepage.manage')->name('homepage-merchandising.update');
            Route::get('/homepage-revisions', [HomepageDesignRevisionController::class, 'index'])->middleware('permission:homepage.manage')->name('homepage-revisions.index');
            Route::post('/homepage-revisions', [HomepageDesignRevisionController::class, 'store'])->middleware('permission:homepage.manage')->name('homepage-revisions.store');
            Route::get('/homepage-revisions/{revision}/preview', [HomepageDesignRevisionController::class, 'preview'])->whereNumber('revision')->middleware('permission:homepage.manage')->name('homepage-revisions.preview');
            Route::post('/homepage-revisions/{revision}/publish', [HomepageDesignRevisionController::class, 'publish'])->whereNumber('revision')->middleware('permission:homepage.manage')->name('homepage-revisions.publish');
            Route::delete('/homepage-revisions/{revision}', [HomepageDesignRevisionController::class, 'destroy'])->whereNumber('revision')->middleware('permission:homepage.manage')->name('homepage-revisions.destroy');
            Route::get('/homepage-shelves', [HomepageShelfController::class, 'index'])->middleware('permission:homepage.manage')->name('homepage-shelves.index');
            Route::put('/homepage-shelves', [HomepageShelfController::class, 'update'])->middleware('permission:homepage.manage')->name('homepage-shelves.update');
            Route::get('/banners', [HomepageBannerController::class, 'index'])->middleware('permission:banners.manage')->name('banners.index');
            Route::get('/banners/create', [HomepageBannerController::class, 'create'])->middleware('permission:banners.manage')->name('banners.create');
            Route::post('/banners', [HomepageBannerController::class, 'store'])->middleware('permission:banners.manage')->name('banners.store');
            Route::get('/banners/{banner}/edit', [HomepageBannerController::class, 'edit'])->whereNumber('banner')->middleware('permission:banners.manage')->name('banners.edit');
            Route::put('/banners/{banner}', [HomepageBannerController::class, 'update'])->whereNumber('banner')->middleware('permission:banners.manage')->name('banners.update');
            Route::delete('/banners/{banner}', [HomepageBannerController::class, 'destroy'])->whereNumber('banner')->middleware('permission:banners.manage')->name('banners.destroy');
            Route::get('/catalog', [CatalogManagementController::class, 'index'])->middleware('permission:catalog.manage')->name('catalog.index');
            Route::patch('/catalog/{product}/status', [CatalogManagementController::class, 'updateStatus'])->middleware('permission:catalog.manage')->name('catalog.status');
            Route::get('/sellers', [SellerReviewController::class, 'index'])->middleware('permission:sellers.review')->name('sellers.index');
            Route::post('/sellers/{seller}/decision', [SellerReviewController::class, 'decide'])->middleware('permission:sellers.review')->name('sellers.decide');
        });
    });
});
