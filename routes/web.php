<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\NodeController as AdminNodeController;
use App\Http\Controllers\Admin\ServerController as AdminServerController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Logged-in users go to the dashboard, everyone else to the login page.
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/servers', [ServerController::class, 'index'])->name('servers.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::post('nodes/{node}/refresh', [AdminNodeController::class, 'refresh'])->name('nodes.refresh');
    Route::resource('nodes', AdminNodeController::class)->except('show');
    Route::resource('servers', AdminServerController::class)->except('show');
    Route::resource('users', AdminUserController::class)->except('show');
});

require __DIR__.'/auth.php';
