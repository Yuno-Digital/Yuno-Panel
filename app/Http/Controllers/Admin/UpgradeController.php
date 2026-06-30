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
     * Start the upgrade in the background and stream progress to a log file the
     * dashboard can poll (a full upgrade outlasts a normal web request).
     */
    public function run(): RedirectResponse
    {
        file_put_contents($this->logPath(), "Starting upgrade…\n");

        $command = sprintf(
            'nohup %s artisan yuno:upgrade >> %s 2>&1 &',
            escapeshellarg(PHP_BINARY),
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
