<?php

namespace App\Support;

use App\Models\Plugin;
use App\Models\PluginSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PharData;
use Throwable;

/**
 * Discovers plugins under the panel's plugins/ directory (each a folder with a
 * plugin.json manifest) and tracks which are enabled.
 */
class PluginManager
{
    public static function path(): string
    {
        return base_path('plugins');
    }

    /**
     * All plugins found on disk, keyed by id, with their metadata.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function discover(): array
    {
        $plugins = [];

        foreach (glob(self::path().'/*/plugin.json') ?: [] as $manifest) {
            $meta = json_decode((string) @file_get_contents($manifest), true);
            if (! is_array($meta) || empty($meta['id'])) {
                continue;
            }

            $plugins[$meta['id']] = [
                'id' => (string) $meta['id'],
                'name' => (string) ($meta['name'] ?? $meta['id']),
                'version' => (string) ($meta['version'] ?? ''),
                'description' => (string) ($meta['description'] ?? ''),
                'author' => (string) ($meta['author'] ?? ''),
                'namespace' => $meta['namespace'] ?? null,
                'provider' => $meta['provider'] ?? null,
                'settings' => is_array($meta['settings'] ?? null) ? $meta['settings'] : [],
                'path' => dirname($manifest),
            ];
        }

        ksort($plugins);

        return $plugins;
    }

    /**
     * Ids of enabled plugins (empty if the table doesn't exist yet).
     *
     * @return array<int, string>
     */
    public static function enabledIds(): array
    {
        try {
            return Plugin::where('enabled', true)->pluck('id')->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Discovered plugins merged with their enabled state, for the admin UI.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $enabled = self::enabledIds();

        return array_values(array_map(
            fn (array $p) => $p + ['enabled' => in_array($p['id'], $enabled, true)],
            self::discover(),
        ));
    }

    /**
     * Enabled plugins that also exist on disk (for the loader).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function enabled(): array
    {
        $enabled = self::enabledIds();

        return array_filter(self::discover(), fn (array $p) => in_array($p['id'], $enabled, true));
    }

    /**
     * Plugins available in the plugins repository (from its registry.json),
     * excluding those already installed. Cached briefly.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function available(): array
    {
        $installed = array_keys(self::discover());

        try {
            $registry = cache()->remember('plugins.registry', now()->addMinutes(10), function () {
                $url = sprintf(
                    'https://raw.githubusercontent.com/%s/%s/registry.json',
                    config('yuno.plugins_repository'),
                    config('yuno.plugins_branch'),
                );

                return Http::timeout(10)->get($url)->json('plugins') ?? [];
            });
        } catch (Throwable) {
            return [];
        }

        return array_values(array_filter(
            is_array($registry) ? $registry : [],
            fn ($p) => is_array($p) && ! empty($p['id']) && ! in_array($p['id'], $installed, true),
        ));
    }

    /**
     * A single stored setting for a plugin (falls back to $default / env).
     */
    public static function setting(string $pluginId, string $key, mixed $default = null): mixed
    {
        try {
            $value = PluginSetting::where('plugin_id', $pluginId)->where('key', $key)->value('value');
        } catch (Throwable) {
            $value = null;
        }

        return $value ?? $default;
    }

    /**
     * All stored settings for a plugin as key => value.
     *
     * @return array<string, mixed>
     */
    public static function settingsFor(string $pluginId): array
    {
        try {
            return PluginSetting::where('plugin_id', $pluginId)->pluck('value', 'key')->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Persist a plugin's settings (key => value).
     *
     * @param  array<string, mixed>  $values
     */
    public static function saveSettings(string $pluginId, array $values): void
    {
        foreach ($values as $key => $value) {
            PluginSetting::updateOrCreate(
                ['plugin_id' => $pluginId, 'key' => $key],
                ['value' => $value === '' ? null : $value],
            );
        }
    }

    /**
     * Download and install a plugin by id from the plugins repository. Returns
     * true on success.
     */
    public static function install(string $id): bool
    {
        $branch = config('yuno.plugins_branch');
        $repo = config('yuno.plugins_repository');
        $tmp = storage_path('app/plugin-install-'.Str::random(8));

        try {
            File::ensureDirectoryExists($tmp);

            $archive = $tmp.'/repo.tar.gz';
            $url = sprintf('https://codeload.github.com/%s/tar.gz/refs/heads/%s', $repo, $branch);

            $response = Http::timeout(60)->get($url);
            if (! $response->successful()) {
                return false;
            }
            File::put($archive, $response->body());

            (new PharData($archive))->extractTo($tmp.'/extracted', null, true);

            // The tarball extracts to "<repo-name>-<branch>/<plugin id>/".
            $repoName = Str::afterLast($repo, '/');
            $source = $tmp.'/extracted/'.$repoName.'-'.$branch.'/'.$id;
            if (! is_dir($source) || ! is_file($source.'/plugin.json')) {
                return false;
            }

            File::copyDirectory($source, self::path().'/'.$id);

            return true;
        } catch (Throwable) {
            return false;
        } finally {
            File::deleteDirectory($tmp);
        }
    }
}
