<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Checks the latest released version of the Wings node daemon on GitHub, so the
 * panel can flag nodes running an older daemon.
 */
class WingsUpdateChecker
{
    private const CACHE_KEY = 'yuno.wings_latest_release';

    /**
     * Latest released daemon version (without a leading "v"), or null when the
     * repository has no releases or GitHub can't be reached.
     */
    public function latestVersion(): ?string
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            $repo = config('yuno.wings_repository');

            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Accept' => 'application/vnd.github+json',
                        'User-Agent' => 'Yuno-Panel',
                    ])
                    ->get("https://api.github.com/repos/{$repo}/releases/latest");
            } catch (Throwable) {
                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            $tag = ltrim((string) $response->json('tag_name'), 'vV');

            return $tag !== '' ? $tag : null;
        });
    }

    /**
     * Whether $installed is older than the latest release.
     */
    public function updateAvailable(?string $installed): bool
    {
        $latest = $this->latestVersion();

        return $latest !== null && $installed !== null && $installed !== ''
            && version_compare($latest, $installed, '>');
    }
}
