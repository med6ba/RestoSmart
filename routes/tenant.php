<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AdminController;
use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\CheckoutController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\DriverController;
use App\Http\Controllers\Tenant\KitchenController;
use App\Http\Controllers\Tenant\MenuController;
use App\Http\Controllers\Tenant\OrderController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::prefix('{tenant}')
    ->middleware(['web', InitializeTenancyByPath::class])
    ->name('tenant.')
    ->where(['tenant' => '^(?!(?:login|register|dashboard|restaurant|profile|locale|logout|password|verify-email|confirm-password|email|plans|tenants|applications|storage|build|images|up)$)[a-z0-9][a-z0-9-]*$'])
    ->group(function () {
        Route::get('/', MenuController::class)->name('menu');

        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthController::class, 'login'])->name('login');
            Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
            Route::get('/register', [AuthController::class, 'register'])->name('register');
            Route::post('/register', [AuthController::class, 'store'])->name('register.store');
        });

        Route::post('/cart/{menuItem}', [CartController::class, 'add'])->name('cart.add');
        Route::patch('/cart/{menuItem}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/dashboard', DashboardController::class)->name('dashboard');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{order}/status', [OrderController::class, 'status'])->name('orders.status');

            Route::middleware('role:client')->group(function () {
                Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
                Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
                Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            });

            Route::middleware('role:kitchen')->group(function () {
                Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen');
                Route::post('/kitchen/orders/{order}/preparing', [KitchenController::class, 'preparing'])->name('kitchen.preparing');
                Route::post('/kitchen/orders/{order}/ready', [KitchenController::class, 'ready'])->name('kitchen.ready');
                Route::post('/kitchen/orders/{order}/collected', [KitchenController::class, 'collected'])->name('kitchen.collected');
            });

            Route::middleware('role:driver')->group(function () {
                Route::get('/driver', [DriverController::class, 'index'])->name('driver');
                Route::post('/driver/orders/{order}/pickup', [DriverController::class, 'pickup'])->name('driver.pickup');
                Route::post('/driver/orders/{order}/deliver', [DriverController::class, 'deliver'])->name('driver.deliver');
                Route::patch('/driver/orders/{order}/location', [DriverController::class, 'location'])->name('driver.location');
            });

            Route::middleware('role:admin')->group(function () {
                Route::get('/admin', [AdminController::class, 'index'])->name('admin');
                Route::post('/admin/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
                Route::post('/admin/menu-items', [AdminController::class, 'storeMenuItem'])->name('admin.menu-items.store');
                Route::post('/admin/staff', [AdminController::class, 'inviteStaff'])->name('admin.staff.store');
                Route::post('/admin/stock-adjustments', [AdminController::class, 'adjustStock'])->name('admin.stock.adjust');
                Route::post('/admin/tables', [AdminController::class, 'configureTables'])->name('admin.tables.configure');
                Route::get('/admin/tables/{restaurantTable}/qr', [AdminController::class, 'tableQr'])->name('admin.tables.qr');
                Route::post('/admin/orders/{order}/assign', [AdminController::class, 'assign'])->name('admin.orders.assign');
            });
        });
    });
