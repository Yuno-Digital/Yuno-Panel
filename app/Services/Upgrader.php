<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Throwable;

/**
 * Performs an in-place upgrade of the panel: pull the latest code, install
 * dependencies, run migrations and rebuild assets. Mirrors what you'd do by
 * hand on a git-deployed install.
 */
class Upgrader
{
    /**
     * The ordered steps of an upgrade.
     *
     * @return array<int, array{label: string, cmd: array<int, string>}>
     */
    public function steps(): array
    {
        return [
            ['label' => 'Fetch latest code', 'cmd' => ['git', 'fetch', '--all', '--tags', '--prune']],
            ['label' => 'Pull updates', 'cmd' => ['git', 'pull', '--ff-only']],
            ['label' => 'Install PHP dependencies', 'cmd' => ['composer', 'install', '--no-interaction', '--no-dev', '--prefer-dist', '--optimize-autoloader']],
            ['label' => 'Run migrations', 'cmd' => ['php', 'artisan', 'migrate', '--force']],
            ['label' => 'Install JS dependencies', 'cmd' => ['npm', 'ci', '--include=dev']],
            ['label' => 'Build assets', 'cmd' => ['npm', 'run', 'build']],
            ['label' => 'Clear caches', 'cmd' => ['php', 'artisan', 'optimize:clear']],
        ];
    }

    /**
     * Run every step in order, stopping on the first failure. Each output line
     * is passed to $onLine (if given) and collected into the returned log.
     *
     * @return array{0: bool, 1: array<int, string>}
     */
    public function run(?callable $onLine = null): array
    {
        $log = [];
        $emit = function (string $line) use (&$log, $onLine): void {
            $log[] = $line;
            if ($onLine) {
                $onLine($line);
            }
        };

        // Run as the web user (e.g. www-data), whose HOME is often unwritable
        // (/var/www) or littered with root-owned caches from earlier sudo runs.
        // Point HOME and the package-manager caches at a dir we own (storage/)
        // so composer/npm don't try to write to /var/www/.npm or /var/www/.cache.
        $home = storage_path('framework/upgrade');
        if (! is_dir($home)) {
            @mkdir($home, 0775, true);
        }
        $env = [
            'HOME' => $home,
            'COMPOSER_HOME' => $home.'/composer',
            'COMPOSER_CACHE_DIR' => $home.'/composer/cache',
            'npm_config_cache' => $home.'/npm-cache',
        ];

        foreach ($this->steps() as $step) {
            $emit('$ '.implode(' ', $step['cmd']));

            $process = new Process($step['cmd'], base_path(), $env);
            $process->setTimeout(600);

            try {
                $process->run(function (string $type, string $buffer) use ($emit): void {
                    foreach (preg_split('/\r?\n/', rtrim($buffer)) as $line) {
                        if ($line !== '') {
                            $emit($line);
                        }
                    }
                });
            } catch (Throwable $e) {
                $emit('ERROR: '.$e->getMessage());

                return [false, $log];
            }

            if (! $process->isSuccessful()) {
                $emit('✗ Step failed: '.$step['label']);

                return [false, $log];
            }
        }

        $emit('✓ Upgrade complete.');

        return [true, $log];
    }
}
