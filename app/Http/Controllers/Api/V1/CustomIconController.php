<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomIcon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomIconController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $icons = CustomIcon::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $icons
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/i',
                'unique:custom_icons,name'
            ],
            'svg_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $trimmed = trim($value);
                    if (!str_starts_with($trimmed, '<svg') || !str_ends_with($trimmed, '</svg>')) {
                        $fail('The svg code field must be a valid SVG markup starting with <svg> and ending with </svg>.');
                    }
                }
            ],
            'category' => 'nullable|string|max:50'
        ]);

        $icon = CustomIcon::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Custom icon created successfully.',
            'data' => $icon
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomIcon $customIcon): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $customIcon
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomIcon $customIcon): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/i',
                Rule::unique('custom_icons', 'name')->ignore($customIcon->id)
            ],
            'svg_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $trimmed = trim($value);
                    if (!str_starts_with($trimmed, '<svg') || !str_ends_with($trimmed, '</svg>')) {
                        $fail('The svg code field must be a valid SVG markup starting with <svg> and ending with </svg>.');
                    }
                }
            ],
            'category' => 'nullable|string|max:50'
        ]);

        $customIcon->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Custom icon updated successfully.',
            'data' => $customIcon
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomIcon $customIcon): JsonResponse
    {
        $customIcon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Custom icon deleted successfully.'
        ]);
    }
}
