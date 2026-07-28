<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIfInstalled
{
    public function handle(Request $request, Closure $next)
    {
        // If the system is already installed, block access to installer
        if (file_exists(storage_path('installed.lock'))) {
            return redirect('/');
        }

        return $next($request);
    }
}
