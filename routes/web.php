<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AllocationController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\DatabaseHostController;
use App\Http\Controllers\Admin\EggController;
use App\Http\Controllers\Admin\EggImportController;
use App\Http\Controllers\Admin\EggVariableController;
use App\Http\Controllers\Admin\NodeController as AdminNodeController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServerController as AdminServerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UpgradeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\ClientApiKeyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\NodeConfigController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

// Web installer (gated: redirects away once the panel is installed).
Route::get('/install', [InstallController::class, 'show'])->name('install.show');
Route::post('/install', [InstallController::class, 'store'])->name('install.store');

Route::get('/', function () {
    // Logged-in users go to the dashboard, everyone else to the login page.
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/servers', [ServerController::class, 'index'])->name('servers.index');
    Route::get('/servers/{server}', [ServerController::class, 'show'])->name('servers.show');
    Route::patch('/servers/{server}', [ServerController::class, 'update'])->name('servers.update');
    Route::post('/servers/{server}/install', [ServerController::class, 'install'])->name('servers.install');
    Route::post('/servers/{server}/power', [ServerController::class, 'power'])->name('servers.power');
    Route::post('/servers/{server}/command', [ServerController::class, 'command'])->name('servers.command');
    Route::get('/servers/{server}/ws', [ServerController::class, 'websocket'])->name('servers.ws');
    Route::get('/servers/{server}/stats', [ServerController::class, 'stats'])->name('servers.stats');
    Route::get('/servers/{server}/logs', [ServerController::class, 'logs'])->name('servers.logs');
    Route::get('/servers/{server}/files/list', [ServerController::class, 'files'])->name('servers.files');
    Route::get('/servers/{server}/files/contents', [ServerController::class, 'fileRead'])->name('servers.files.read');
    Route::post('/servers/{server}/files/write', [ServerController::class, 'fileWrite'])->name('servers.files.write');
    Route::post('/servers/{server}/files/delete', [ServerController::class, 'fileDelete'])->name('servers.files.delete');
    Route::post('/servers/{server}/subusers', [ServerController::class, 'storeSubuser'])->name('servers.subusers.store');
    Route::delete('/servers/{server}/subusers/{user}', [ServerController::class, 'destroySubuser'])->name('servers.subusers.destroy');
    Route::post('/servers/{server}/schedules', [ServerController::class, 'storeSchedule'])->name('servers.schedules.store');
    Route::patch('/servers/{server}/schedules/{schedule}', [ServerController::class, 'toggleSchedule'])->name('servers.schedules.toggle');
    Route::delete('/servers/{server}/schedules/{schedule}', [ServerController::class, 'destroySchedule'])->name('servers.schedules.destroy');
    Route::post('/servers/{server}/webhooks', [ServerController::class, 'storeWebhook'])->name('servers.webhooks.store');
    Route::patch('/servers/{server}/webhooks/{webhook}', [ServerController::class, 'toggleWebhook'])->name('servers.webhooks.toggle');
    Route::delete('/servers/{server}/webhooks/{webhook}', [ServerController::class, 'destroyWebhook'])->name('servers.webhooks.destroy');
    Route::post('/servers/{server}/databases', [ServerController::class, 'storeDatabase'])->name('servers.databases.store');
    Route::patch('/servers/{server}/databases/{database}', [ServerController::class, 'rotateDatabase'])->name('servers.databases.rotate');
    Route::delete('/servers/{server}/databases/{database}', [ServerController::class, 'destroyDatabase'])->name('servers.databases.destroy');
    Route::post('/servers/{server}/allocations', [ServerController::class, 'addAllocation'])->name('servers.allocations.add');
    Route::patch('/servers/{server}/allocations/{allocation}', [ServerController::class, 'makePrimaryAllocation'])->name('servers.allocations.primary');
    Route::delete('/servers/{server}/allocations/{allocation}', [ServerController::class, 'removeAllocation'])->name('servers.allocations.remove');
    Route::post('/servers/{server}/backups', [ServerController::class, 'storeBackup'])->name('servers.backups.store');
    Route::get('/servers/{server}/backups/{backup}/download', [ServerController::class, 'downloadBackup'])->name('servers.backups.download');
    Route::post('/servers/{server}/backups/{backup}/restore', [ServerController::class, 'restoreBackup'])->name('servers.backups.restore');
    Route::delete('/servers/{server}/backups/{backup}', [ServerController::class, 'destroyBackup'])->name('servers.backups.destroy');
    // Deep-linkable UI tab, e.g. /servers/5/startup — resolves to the show page.
    Route::get('/servers/{server}/{tab}', [ServerController::class, 'show'])
        ->whereIn('tab', ['console', 'files', 'databases', 'backups', 'schedules', 'activity', 'network', 'startup', 'subusers', 'webhooks', 'settings'])->name('servers.show.tab');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Theme preference (light / dark / system).
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');

    // Client API keys are managed by each user from their profile.
    Route::post('/profile/api-keys', [ClientApiKeyController::class, 'store'])->name('profile.api-keys.store');
    Route::delete('/profile/api-keys/{apiKey}', [ClientApiKeyController::class, 'destroy'])->name('profile.api-keys.destroy');

    // Two-factor authentication setup.
    Route::post('/profile/two-factor', [TwoFactorController::class, 'store'])->name('profile.2fa.store');
    Route::post('/profile/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('profile.2fa.confirm');
    Route::delete('/profile/two-factor', [TwoFactorController::class, 'destroy'])->name('profile.2fa.destroy');
    Route::get('/profile/two-factor/recovery-codes', [TwoFactorController::class, 'downloadRecoveryCodes'])->name('profile.2fa.recovery');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::post('upgrade', [UpgradeController::class, 'run'])->name('upgrade.run');
    Route::get('upgrade/log', [UpgradeController::class, 'log'])->name('upgrade.log');
    Route::post('nodes/{node}/refresh', [AdminNodeController::class, 'refresh'])->name('nodes.refresh');
    Route::post('nodes/{node}/upgrade', [AdminNodeController::class, 'upgrade'])->name('nodes.upgrade');
    Route::post('nodes/{node}/regenerate-token', [AdminNodeController::class, 'regenerateToken'])->name('nodes.regenerate-token');
    Route::post('nodes/{node}/allocations', [AllocationController::class, 'store'])->name('nodes.allocations.store');
    Route::delete('nodes/{node}/allocations/{allocation}', [AllocationController::class, 'destroy'])->name('nodes.allocations.destroy');
    Route::resource('nodes', AdminNodeController::class)->except('show');
    Route::resource('servers', AdminServerController::class)->except('show');
    Route::resource('users', AdminUserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('database-hosts', DatabaseHostController::class)->except('show');
    Route::resource('webhooks', WebhookController::class)->except('show');
    Route::post('eggs/import', [EggImportController::class, 'store'])->name('eggs.import');
    Route::resource('eggs', EggController::class)->except('show');
    Route::post('eggs/{egg}/variables', [EggVariableController::class, 'store'])->name('eggs.variables.store');
    Route::put('eggs/{egg}/variables/{variable}', [EggVariableController::class, 'update'])->name('eggs.variables.update');
    Route::delete('eggs/{egg}/variables/{variable}', [EggVariableController::class, 'destroy'])->name('eggs.variables.destroy');

    Route::get('plugins', [PluginController::class, 'index'])->name('plugins.index');
    Route::post('plugins/install', [PluginController::class, 'install'])->name('plugins.install');
    Route::post('plugins/{plugin}/update', [PluginController::class, 'update'])->name('plugins.update');
    Route::post('plugins/clear-cache', [PluginController::class, 'clearCache'])->name('plugins.clear-cache');
    Route::get('plugins/{plugin}/settings', [PluginController::class, 'settings'])->name('plugins.settings');
    Route::put('plugins/{plugin}/settings', [PluginController::class, 'updateSettings'])->name('plugins.settings.update');
    Route::post('plugins/{plugin}/toggle', [PluginController::class, 'toggle'])->name('plugins.toggle');
    Route::delete('plugins/{plugin}', [PluginController::class, 'uninstall'])->name('plugins.uninstall');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('api/application', [ApiKeyController::class, 'application'])->name('api.application.index');
    Route::post('api/application', [ApiKeyController::class, 'storeApplication'])->name('api.application.store');
    Route::delete('api/keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api.keys.destroy');
});

// Node daemon configuration, fetched by `wings configure` (token-authenticated).
Route::get('/api/nodes/{node}/config', [NodeConfigController::class, 'show'])->name('nodes.config');

// Two-factor login challenge (after password, before the session is authenticated).
Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
    ->middleware('guest')->name('two-factor.challenge');
Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
    ->middleware('guest')->name('two-factor.challenge.store');

require __DIR__.'/auth.php';
