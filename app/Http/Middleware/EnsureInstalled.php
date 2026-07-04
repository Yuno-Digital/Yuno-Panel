<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Until the panel is installed, send every request to the web installer.
 */
class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Installer::isInstalled() || $request->is('install', 'install/*', 'up', 'build/*')) {
            return $next($request);
        }

        return redirect()->route('install.show');
    }
}
