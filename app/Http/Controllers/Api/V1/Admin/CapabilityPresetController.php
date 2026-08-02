<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapabilityPreset;
use Illuminate\Http\Request;

class CapabilityPresetController extends Controller
{
    /**
     * Display a listing of the presets.
     */
    public function index()
    {
        $presets = CapabilityPreset::orderBy('group')
            ->orderBy('name')
            ->get();
            
        return $this->successResponse($presets);
    }

    /**
     * Store a newly created preset.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'nullable|string|max:100',
            'translations' => 'nullable|array',
        ]);

        $preset = CapabilityPreset::create($validated);

        return $this->successResponse($preset, 'Capability preset created successfully.', 201);
    }

    /**
     * Display the specified preset.
     */
    public function show(CapabilityPreset $preset)
    {
        return $this->successResponse($preset);
    }

    /**
     * Update the specified preset.
     */
    public function update(Request $request, CapabilityPreset $preset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'nullable|string|max:100',
            'translations' => 'nullable|array',
        ]);

        $preset->update($validated);

        return $this->successResponse($preset, 'Capability preset updated successfully.');
    }

    /**
     * Remove the specified preset.
     */
    public function destroy(CapabilityPreset $preset)
    {
        $preset->delete();

        return $this->successResponse(null, 'Capability preset deleted successfully.');
    }
}
