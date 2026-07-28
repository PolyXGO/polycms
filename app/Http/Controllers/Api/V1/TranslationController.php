<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Helpers\LanguageHelper;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    /**
     * Get all translations for current language
     */
    public function index(): JsonResponse
    {
        // Ensure LanguageHelper is initialized
        LanguageHelper::init();
        
        $locale = LanguageHelper::getCurrentLanguage();
        
        // Get translations from LanguageHelper using reflection
        $reflection = new \ReflectionClass(LanguageHelper::class);
        $property = $reflection->getProperty('translations');
        $property->setAccessible(true);
        $translations = $property->getValue();
        
        return response()->json([
            'success' => true,
            'data' => [
                'locale' => $locale,
                'translations' => $translations[$locale] ?? [],
            ],
        ]);
    }
}

