<?php

namespace App\Providers;

use App\Support\PluginManager;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Loads enabled plugins: registers each plugin's PSR-4 namespace and its
 * service provider. A broken or missing plugin is logged, never fatal.
 */
class PluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (PluginManager::enabled() as $plugin) {
            try {
                if (! empty($plugin['namespace']) && is_dir($plugin['path'].'/src')) {
                    $loader = require base_path('vendor/autoload.php');
                    $loader->addPsr4(rtrim($plugin['namespace'], '\\').'\\', $plugin['path'].'/src');
                }

                if (! empty($plugin['provider']) && class_exists($plugin['provider'])) {
                    $this->app->register($plugin['provider']);
                }
            } catch (Throwable $e) {
                report($e);
            }
        }
    }
}
