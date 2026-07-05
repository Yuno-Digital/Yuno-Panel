<?php

namespace App\Support;

/**
 * A tiny extension point that lets plugins theme the panel. A plugin's service
 * provider can push CSS/`<link>`/`<style>` (or a callback returning HTML) into
 * the page <head>; it is rendered after the app stylesheet, so plugin styles
 * override the defaults. Recolour the signature accent by overriding the
 * --yuno-accent-* CSS variables.
 */
class Theme
{
    /** @var array<int, string|callable():string> */
    protected static array $head = [];

    /**
     * Register head markup (a string or a callback returning one).
     */
    public static function head(string|callable $html): void
    {
        static::$head[] = $html;
    }

    /**
     * Render all registered head markup.
     */
    public static function renderHead(): string
    {
        return collect(static::$head)
            ->map(fn ($item) => is_callable($item) ? (string) $item() : (string) $item)
            ->implode("\n");
    }

    /**
     * Forget all registered contributions (used in tests).
     */
    public static function flush(): void
    {
        static::$head = [];
    }
}
