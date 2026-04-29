<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect logic based on login source and roles
        $user = $request->user();
        $isFromAccountLogin = $request->header('referer') && str_contains($request->header('referer'), 'account/login');

        if ($user->hasAnyRole(['admin', 'editor', 'author'])) {
            if ($isFromAccountLogin) {
                // If logging in from account page, stay in account area but force reload for topbar
                return Inertia::location(redirect()->intended(route('dashboard'))->getTargetUrl());
            }
            // Otherwise force redirect to Admin SPA
            return Inertia::location(route('admin.dashboard', absolute: false));
        }

        if ($user->hasRole('customer')) {
            return Inertia::location(redirect()->intended(route('dashboard'))->getTargetUrl());
        }

        return Inertia::location(url('/'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        // Logout from web guard (session)
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->header('X-Inertia')) {
            return \Inertia\Inertia::location(url('/'));
        }

        return redirect()->to(url('/'));
    }
}
