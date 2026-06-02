<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlatformDashboardController;
use App\Http\Controllers\PlatformUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantApplicationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TenantLifecycleController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::post('/locale', LocaleController::class)->name('locale.switch');
Route::get('/restaurant/apply', [RestaurantApplicationController::class, 'create'])->name('restaurants.apply');
Route::post('/restaurant/apply', [RestaurantApplicationController::class, 'store'])->name('restaurants.apply.store');

Route::get('/dashboard', PlatformDashboardController::class)
    ->middleware(['auth', 'verified', 'role:super,admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/settings', SettingsController::class)->name('settings');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/impersonation/stop', [PlatformUserController::class, 'stopImpersonating'])->name('impersonation.stop');
    
    Route::get('/api/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/api/notifications/read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read');
});

Route::middleware(['auth', 'role:super'])->group(function () {
    Route::get('/users', [PlatformUserController::class, 'index'])->name('platform.users.index');
    Route::get('/users/{role}', [PlatformUserController::class, 'role'])
        ->where('role', 'admin|kitchen|driver|client')
        ->name('platform.users.role');
    Route::post('/users/{user}/impersonate', [PlatformUserController::class, 'impersonate'])->name('platform.users.impersonate');
    Route::post('/applications/{application}/approve', [RestaurantApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [RestaurantApplicationController::class, 'reject'])->name('applications.reject');
    Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
    Route::patch('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::patch('/tenants/{tenant}', [TenantLifecycleController::class, 'update'])->name('tenants.lifecycle.update');
    Route::get('/payments', [\App\Http\Controllers\PlatformPaymentController::class, 'index'])->name('platform.payments.index');
});

require __DIR__.'/auth.php';
