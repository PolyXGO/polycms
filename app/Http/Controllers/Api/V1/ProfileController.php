<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Get current user profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        return $this->successResponse(
            new UserResource($user),
            'Profile retrieved successfully'
        );
    }

    /**
     * Update current user profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            // Email is not allowed to be updated from profile page
            'password' => ['sometimes', 'nullable', 'confirmed', Password::defaults()],
            'current_password' => ['required_with:password', 'current_password'],
        ]);

        // Update name only (email cannot be changed from profile page)
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        // Update password if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->load('roles');

        return $this->successResponse(
            new UserResource($user),
            'Profile updated successfully'
        );
    }
}
