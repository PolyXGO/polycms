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
        return is_admin_user($request);
    }
}
