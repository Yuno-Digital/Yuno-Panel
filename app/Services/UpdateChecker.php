<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Checks whether a newer panel version has been released on GitHub.
 */
class UpdateChecker
{
    private const CACHE_KEY = 'yuno.latest_release';

    /**
     * The version this panel is currently running.
     */
    public function current(): string
    {
        return (string) config('yuno.version');
    }

    /**
     * Determine the update status, comparing the current version against the
     * latest GitHub release.
     *
     * @return array{current: string, checked: bool, available: bool, latest: ?string, changelog: ?string, url: ?string}
     */
    public function status(): array
    {
        $current = $this->current();
        $latest = $this->latestRelease();

        if ($latest === null) {
            return [
                'current' => $current,
                'checked' => false,
                'available' => false,
                'latest' => null,
                'changelog' => null,
                'url' => null,
            ];
        }

        $latestVersion = ltrim((string) ($latest['tag'] ?? ''), 'vV');
        $available = $latestVersion !== '' && version_compare($latestVersion, $current, '>');

        return [
            'current' => $current,
            'checked' => true,
            'available' => $available,
            'latest' => $latestVersion ?: null,
            'changelog' => $available ? ($latest['body'] ?? null) : null,
            'url' => $latest['url'] ?? null,
        ];
    }

    /**
     * Fetch (and cache) the latest release from GitHub. Returns null when the
     * repository has no releases or GitHub cannot be reached.
     *
     * @return array{tag: ?string, body: ?string, url: ?string}|null
     */
    private function latestRelease(): ?array
    {
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        $repo = config('yuno.repository');

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => 'Yuno-Panel',
                ])
                ->get("https://api.github.com/repos/{$repo}/releases/latest");
        } catch (Throwable) {
            // Transient failure (e.g. offline) — don't cache, retry next time.
            return null;
        }

        if ($response->status() === 404) {
            // No releases published yet — a successful check with nothing newer.
            $none = ['tag' => null, 'body' => null, 'url' => null];
            Cache::put(self::CACHE_KEY, $none, now()->addMinutes(30));

            return $none;
        }

        if (! $response->successful()) {
            // Rate limited / server error — treat as "couldn't check", retry later.
            return null;
        }

        $release = [
            'tag' => $response->json('tag_name'),
            'body' => $response->json('body'),
            'url' => $response->json('html_url'),
        ];

        Cache::put(self::CACHE_KEY, $release, now()->addHour());

        return $release;
    }
}
