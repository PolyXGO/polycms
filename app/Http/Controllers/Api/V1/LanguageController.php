<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    protected LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $languages = Language::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $languages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
            'direction' => 'required|in:ltr,rtl',
            'flag' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_default'] = false; // Default can only be set via explicit action

        $language = Language::create($validated);

        // Copy translations from default language
        $defaultLang = Language::where('is_default', true)->first();
        if ($defaultLang) {
            $this->languageService->copyTranslations($defaultLang->code, $language->code);
        }

        return response()->json([
            'success' => true,
            'message' => 'Language created successfully',
            'data' => $language,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Language $language): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'native_name' => 'sometimes|string|max:255',
            'direction' => 'sometimes|in:ltr,rtl',
            'flag' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // Handle default language switch
        if (isset($validated['is_default']) && $validated['is_default']) {
            Language::where('is_default', true)->update(['is_default' => false]);
            $language->update(['is_default' => true]);
            $validated['is_active'] = true; // Default must be active
        }

        $language->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => $language,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language): JsonResponse
    {
        if ($language->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the default language.',
            ], 422);
        }

        $language->delete();
        // Ideally we should optionally delete files, but keeping them is safer for now

        return response()->json([
            'success' => true,
            'message' => 'Language deleted successfully',
        ]);
    }

    /**
     * Sync missing translation keys from default language
     */
    public function sync(Request $request, Language $language): JsonResponse
    {
        $scopeId = $request->query('scope', 'core');

        $defaultLang = Language::where('is_default', true)->first();
        if (!$defaultLang) {
            return response()->json([
                'success' => false,
                'message' => 'No default language found.',
            ], 404);
        }

        if ($defaultLang->id === $language->id) {
             return response()->json([
                'success' => false,
                'message' => 'Cannot sync default language with itself.',
            ], 422);
        }

        $count = $this->languageService->syncKeys($defaultLang->code, $language->code, $scopeId);

        return response()->json([
            'success' => true,
            'message' => "Synced {$count} keys from {$defaultLang->name}.",
        ]);
    }

    /**
     * Download translation files
     */
    public function download(Request $request, Language $language)
    {
        $scopeId = $request->query('scope', 'core');

        if ($scopeId === 'core') {
            $path = $this->languageService->getScopeJsonPath($language->code, 'core');
            if (!File::exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Translation file not found.',
                ], 404);
            }
            return response()->download($path, "{$language->code}.json");
        } else {
            // For other scopes, download the specific file
            $path = $this->languageService->getScopeJsonPath($language->code, $scopeId);
            if (!$path || !File::exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scope translation file not found.',
                ], 404);
            }
            $cleanScopeId = str_replace([':', '.'], '_', $scopeId);
            return response()->download($path, "{$language->code}_{$cleanScopeId}.json");
        }
    }

    /**
     * Upload translation files
     */
    public function upload(Request $request, Language $language): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:zip,json|max:10240', // 10MB max, added json support
        ]);

        $scopeId = $request->query('scope', 'core');
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();

        if ($ext === 'json') {
             // Handle single JSON file upload
             try {
                $destPath = $this->languageService->getScopeJsonPath($language->code, $scopeId);
                if (!$destPath) {
                    throw new \Exception("Invalid scope");
                }
                $file->move(dirname($destPath), basename($destPath));
                
                // Trigger compilation
                $this->languageService->compileToPhp($language->code, $scopeId);
                $success = true;
             } catch (\Exception $e) {
                $success = false;
             }
        } else {
             // Handle ZIP (currently unzips logic is global, ignore scope for ZIPs for now or handle later)
             $success = $this->languageService->unpackTranslations($language->code, $file->path());
        }

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process translations.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Translations uploaded successfully.',
        ]);
    }

    /**
     * Get all translations for a language
     */
    public function getTranslations(Request $request, Language $language): JsonResponse
    {
        $scopeId = $request->query('scope', 'core');
        
        $defaultLang = Language::where('is_default', true)->first();
        $defaultCode = $defaultLang?->code ?? 'en';

        // Get available scopes
        $availableScopes = $this->languageService->getAvailableScopes();

        // Validate requested scope
        $scope = $this->languageService->getScope($scopeId);
        if (!$scope) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid scope selected.',
            ], 404);
        }

        // Get default language translations as reference
        $defaultPath = $this->languageService->getScopeJsonPath($defaultCode, $scopeId);
        $defaultTranslations = [];
        if ($defaultPath && File::exists($defaultPath)) {
            $defaultTranslations = json_decode(File::get($defaultPath), true) ?? [];
        }

        // Get target language translations
        $targetPath = $this->languageService->getScopeJsonPath($language->code, $scopeId);
        $targetTranslations = [];
        if ($targetPath && File::exists($targetPath)) {
            $targetTranslations = json_decode(File::get($targetPath), true) ?? [];
        }

        // Build unified list with original and translated values
        $translations = [];
        foreach ($defaultTranslations as $key => $value) {
            $translations[] = [
                'key' => $key,
                'original' => $value,
                'translated' => $targetTranslations[$key] ?? '',
                'is_translated' => isset($targetTranslations[$key]) && $targetTranslations[$key] !== $value,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'language' => $language,
                'translations' => $translations,
                'scopes' => $availableScopes,
                'current_scope' => $scopeId,
            ],
        ]);
    }

    /**
     * Update translations for a language
     */
    public function updateTranslations(Request $request, Language $language): JsonResponse
    {
        $validated = $request->validate([
            'translations' => 'required|array',
            'translations.*.key' => 'required|string',
            'translations.*.translated' => 'nullable|string',
        ]);

        $scopeId = $request->query('scope', 'core');
        $targetPath = $this->languageService->getScopeJsonPath($language->code, $scopeId);
        
        if (!$targetPath) {
             return response()->json([
                'success' => false,
                'message' => 'Invalid scope selected.',
            ], 404);
        }

        // Load existing translations
        $existingTranslations = [];
        if (File::exists($targetPath)) {
            $existingTranslations = json_decode(File::get($targetPath), true) ?? [];
        }

        // Update with new values
        foreach ($validated['translations'] as $item) {
            $existingTranslations[$item['key']] = $item['translated'] ?? '';
        }

        // Save JSON
        if (!File::isDirectory(dirname($targetPath))) {
            File::makeDirectory(dirname($targetPath), 0755, true);
        }
        File::put($targetPath, json_encode($existingTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Compile to PHP
        $this->languageService->compileToPhp($language->code, $scopeId);

        return response()->json([
            'success' => true,
            'message' => 'Translations updated and compiled successfully.',
        ]);
    }

    /**
     * Compile translations from JSON to PHP
     */
    public function compileTranslations(Request $request, Language $language): JsonResponse
    {
        $scopeId = $request->query('scope', 'core');
        
        // If empty scope passed, compile all
        if (empty($scopeId) || $scopeId === 'all') {
            $scopeId = null; 
        }

        $success = $this->languageService->compileToPhp($language->code, $scopeId);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compile translations.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Translations compiled successfully.',
        ]);
    }

    /**
     * Delete a translation key from a language
     */
    public function deleteTranslationKey(Request $request, Language $language): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string',
        ]);

        $scopeId = $request->query('scope', 'core');
        $targetPath = $this->languageService->getScopeJsonPath($language->code, $scopeId);
        
        if (!$targetPath) {
             return response()->json([
                'success' => false,
                'message' => 'Invalid scope selected.',
            ], 404);
        }
        
        // Load existing translations
        $existingTranslations = [];
        if (File::exists($targetPath)) {
            $existingTranslations = json_decode(File::get($targetPath), true) ?? [];
        }

        // Check if key exists
        if (!array_key_exists($validated['key'], $existingTranslations)) {
            return response()->json([
                'success' => false,
                'message' => 'Translation key not found.',
            ], 404);
        }

        // Remove the key
        unset($existingTranslations[$validated['key']]);

        // Save JSON
        File::put($targetPath, json_encode($existingTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Compile to PHP
        $this->languageService->compileToPhp($language->code, $scopeId);

        return response()->json([
            'success' => true,
            'message' => 'Translation key deleted successfully.',
        ]);
    }

    /**
     * Reorder languages
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:languages,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Language::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Languages reordered successfully',
        ]);
    }

    /**
     * Compile translations for all active languages and all scopes
     */
    public function compileAll(Request $request): JsonResponse
    {
        $languages = Language::all();
        $success = true;

        foreach ($languages as $language) {
            if (!$this->languageService->compileToPhp($language->code, null)) {
                $success = false;
            }
        }

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compile some translations.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'All translations compiled successfully.',
        ]);
    }
}
