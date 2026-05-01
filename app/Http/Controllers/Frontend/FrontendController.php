<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

abstract class FrontendController extends Controller
{
    /**
     * Check if the current user is an admin
     * Supports both session-based and token-based authentication
     */
    protected function isAdmin(Request $request): bool
    {
        // First, try session-based authentication
        $user = $request->user();
        if ($user && method_exists($user, 'hasRole')) {
            return $user->hasRole('admin');
        }

        // Try token-based authentication from multiple sources
        $token = null;

        // 1. Try Authorization header
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        // 2. Try query parameter (for preview links) - priority for preview
        if (!$token && $request->has('preview_token')) {
            $token = $request->get('preview_token');
        }

        // 3. Try cookie
        if (!$token) {
            $token = $request->cookie('auth_token');
        }

        // Validate token and check admin role
        if ($token) {
            try {
                // Remove any whitespace
                $token = trim($token);

                // Find token (Sanctum handles token prefix automatically)
                $accessToken = PersonalAccessToken::findToken($token);

                if ($accessToken) {
                    $user = $accessToken->tokenable;
                    if ($user instanceof User && method_exists($user, 'hasRole')) {
                        return $user->hasRole('admin');
                    }
                }
            } catch (\Exception $e) {
                // Log error in development, but don't expose to user
                if (config('app.debug')) {
                    Log::debug('Token validation error in FrontendController::isAdmin', [
                        'error' => $e->getMessage(),
                        'token_preview' => substr($token, 0, 10) . '...',
                    ]);
                }
            }
        }

        return false;
    }
}
