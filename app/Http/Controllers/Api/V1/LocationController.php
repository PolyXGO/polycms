<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Helpers\LocationHelper;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Get all countries with their states
     */
    public function countries(): JsonResponse
    {
        $locations = LocationHelper::getCountriesWithStates();
        
        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }
}
