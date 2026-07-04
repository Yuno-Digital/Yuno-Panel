<?php

namespace App\Support;

use App\Models\Plugin;
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
}
