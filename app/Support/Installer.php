<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Tracks whether the panel has been installed via the web installer and checks
 * server requirements. Once installed, a marker file gates the /install route.
 */
class Installer
{
    /**
     * Path to the "installed" marker file.
     */
    public static function markerPath(): string
    {
        return storage_path('installed');
    }

    /**
     * Whether the panel is installed: either the marker exists, or the database
     * is already set up with at least one user (so existing installs aren't
     * forced back through the installer).
     */
    public static function isInstalled(): bool
    {
        // Don't gate the test suite behind the installer.
        if (app()->runningUnitTests()) {
            return true;
        }

        if (is_file(self::markerPath())) {
            return true;
        }

        try {
            return Schema::hasTable('users') && DB::table('users')->exists();
        } catch (Throwable) {
            // Database not ready yet.
            return false;
        }
    }

    /**
     * Record that installation has completed.
     */
    public static function markInstalled(): void
    {
        @file_put_contents(self::markerPath(), 'installed at '.now()->toIso8601String().PHP_EOL);
    }

    /**
     * Server requirements as a list of ['label' => …, 'ok' => bool].
     *
     * @return array<int, array{label: string, ok: bool}>
     */
    public static function requirements(): array
    {
        return [
            ['label' => 'PHP 8.4 or newer ('.PHP_VERSION.')', 'ok' => version_compare(PHP_VERSION, '8.4.0', '>=')],
            ['label' => 'PDO extension', 'ok' => extension_loaded('pdo')],
            ['label' => 'pdo_sqlite extension', 'ok' => extension_loaded('pdo_sqlite')],
            ['label' => 'mbstring extension', 'ok' => extension_loaded('mbstring')],
            ['label' => 'openssl extension', 'ok' => extension_loaded('openssl')],
            ['label' => 'ctype extension', 'ok' => extension_loaded('ctype')],
            ['label' => 'storage/ writable', 'ok' => is_writable(storage_path())],
            ['label' => 'bootstrap/cache writable', 'ok' => is_writable(base_path('bootstrap/cache'))],
        ];
    }

    /**
     * Whether every requirement is satisfied.
     */
    public static function requirementsMet(): bool
    {
        foreach (self::requirements() as $requirement) {
            if (! $requirement['ok']) {
                return false;
            }
        }

        return true;
    }
}
