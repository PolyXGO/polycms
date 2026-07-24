<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait EnsuresAdmin
{
    protected function ensureAdmin(Request $request): ?JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: admin access required',
            ], 403);
        }

        return null;
    }
}

