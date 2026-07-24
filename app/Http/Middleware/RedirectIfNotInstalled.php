<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        // If the system is already installed, proceed
        if (file_exists(storage_path('installed.lock'))) {
            return $next($request);
        }

        // If not installed, allow access to installation and asset routes
        if ($request->is('install') || $request->is('install/*') || $request->is('build/*') || $request->is('assets/*') || $request->is('themes/*')) {
            return $next($request);
        }

        // Otherwise, redirect to the installation wizard
        return redirect()->route('install.index');
    }
}
