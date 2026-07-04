<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Process\Process;

class UpgradeController extends Controller
{
    private function logPath(): string
    {
        return storage_path('logs/upgrade.log');
    }

    /**
     * A PHP CLI binary that can run artisan. Under php-fpm, PHP_BINARY points at
     * the FPM binary (which only prints usage when handed a script), so fall
     * back to a real CLI binary.
     */
    private function phpBinary(): string
    {
        if (PHP_BINARY && ! str_contains(basename(PHP_BINARY), 'fpm')) {
            return PHP_BINARY;
        }

        foreach (['php8.4', 'php8.3', 'php'] as $candidate) {
            $path = trim((string) @shell_exec('command -v '.escapeshellarg($candidate).' 2>/dev/null'));
            if ($path !== '' && is_executable($path)) {
                return $path;
            }
        }

        foreach (['/usr/bin/php8.4', '/usr/local/bin/php8.4', '/usr/bin/php', '/usr/local/bin/php'] as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        return 'php';
    }

    /**
     * Start the upgrade in the background and stream progress to a log file the
     * dashboard can poll (a full upgrade outlasts a normal web request).
     */
    public function run(): RedirectResponse
    {
        file_put_contents($this->logPath(), "Starting upgrade…\n");

        $command = sprintf(
            'nohup %s artisan yuno:upgrade >> %s 2>&1 &',
            escapeshellarg($this->phpBinary()),
            escapeshellarg($this->logPath()),
        );

        Process::fromShellCommandline($command, base_path())->run();

        return redirect()->route('admin.dashboard')
            ->with('status', __('Upgrade started — progress is shown below.'));
    }

    /**
     * Return the current upgrade log for the dashboard to poll.
     */
    public function log(): JsonResponse
    {
        $path = $this->logPath();

        return response()->json([
            'log' => is_file($path) ? (string) file_get_contents($path) : '',
        ]);
    }
}
