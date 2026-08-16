<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use App\Helpers\LanguageHelper;
use Symfony\Component\HttpFoundation\Response;

class LanguageRoutingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\App\Facades\Hook::applyFilters('language.routing.exclude', false, $request)) {
            return $next($request);
        }

        // Canonical Host Enforcement: Redirect www to non-www (matching APP_URL)
        $host = $request->getHost();
        $appUrl = (string) config('app.url', '');
        $parsedAppUrl = parse_url($appUrl);
        $canonicalHost = $parsedAppUrl['host'] ?? null;

        if ($canonicalHost && !str_starts_with(strtolower($canonicalHost), 'www.') && str_starts_with(strtolower($host), 'www.')) {
            $nonWwwHost = substr($host, 4);
            if (strtolower($nonWwwHost) === strtolower($canonicalHost)) {
                $targetUrl = $request->getScheme() . '://' . $canonicalHost . $request->getRequestUri();
                return redirect()->to($targetUrl, 301);
            }
        }

        $firstSegment = $request->segment(1);

        $activeLocales = cache()->remember('active_languages_non_default', 3600, function () {
            try {
                if (!Schema::hasTable('languages')) {
                    return [];
                }
                return \App\Models\Language::where('is_active', true)
                    ->where('is_default', false)
                    ->pluck('code')
                    ->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });

        $defaultLocale = cache()->remember('default_language_code', 3600, function () {
            try {
                if (!Schema::hasTable('languages')) {
                    return 'en';
                }
                return \App\Models\Language::where('is_default', true)->value('code') ?? 'en';
            } catch (\Exception $e) {
                return 'en';
            }
        });

        // Check if current route is a frontend route
        // Exclude admin, api, install, themes, health checks, login, register, logout, account, sitemaps, and robots.txt
        $isFrontend = ($firstSegment !== 'admin' && 
                       $firstSegment !== 'api' && 
                       $firstSegment !== 'install' && 
                       $firstSegment !== 'themes' && 
                       $firstSegment !== 'modules' && 
                       $firstSegment !== 'storage' && 
                       $firstSegment !== 'assets' && 
                       $firstSegment !== 'build' && 
                       $firstSegment !== 'up' &&
                       $firstSegment !== 'login' &&
                       $firstSegment !== 'register' &&
                       $firstSegment !== 'logout' &&
                       $firstSegment !== 'account' &&
                       $firstSegment !== 'external-auth' &&
                       $firstSegment !== 'robots.txt' &&
                       !str_ends_with((string)$firstSegment, '.xml') &&
                       !str_starts_with((string)$firstSegment, 'sitemap') &&
                       !$request->expectsJson());

        // Default language URLs should stay canonical and unprefixed, e.g. /posts/foo instead of /en/posts/foo.
        if ($isFrontend && $firstSegment === $defaultLocale) {
            $segments = $request->segments();
            array_shift($segments);

            $targetPath = '/' . implode('/', $segments);
            $targetPath = $targetPath === '/' ? '/' : rtrim($targetPath, '/');
            $queryString = http_build_query($request->query());

            return redirect($queryString ? $targetPath . '?' . $queryString : $targetPath, 301);
        }

        // 1. Check if ?lang=... parameter is provided
        $langParam = $request->query('lang');
        if ($langParam && (in_array($langParam, $activeLocales, true) || $langParam === $defaultLocale)) {
            if ($request->hasSession()) {
                session(['locale' => $langParam]);
            }

            // Save user language preference if logged in
            $user = auth()->guard('web')->user() ?? auth()->guard('sanctum')->user() ?? auth()->user();
            if ($user) {
                app(\App\Services\SettingsService::class)->set("user_language_{$user->id}", $langParam, 'user_preferences', 'string', false);
                cache()->forget("user_pref_lang_{$user->id}");
            }
            
            if ($isFrontend) {
                // Redirect to the clean localized URL to remove ?lang= parameter from the query string
                $query = $request->query();
                unset($query['lang']);
                $queryString = http_build_query($query);
                
                // Build target path
                $path = $request->path();
                
                // Strip any existing locale prefix from path
                $segments = explode('/', $path);
                if (!empty($segments) && in_array($segments[0], $activeLocales, true)) {
                    array_shift($segments);
                }
                $cleanPath = implode('/', $segments);

                // --- SMART TRANSLATION ROUTING (ON LANGUAGE SWITCH) ---
                $model = $this->resolveModelForPath($cleanPath);
                if ($model && isset($model->translation_group_id) && $model->translation_group_id) {
                    $groupId = $model->translation_group_id;
                    $translation = $model->newQuery()
                        ->withoutGlobalScope('locale')
                        ->where('translation_group_id', $groupId)
                        ->where('locale', $langParam)
                        ->first();

                    if ($translation) {
                        $targetUrl = $translation->frontend_url;
                        $targetUrl = str_starts_with($targetUrl, 'http') ? parse_url($targetUrl, PHP_URL_PATH) : $targetUrl;
                        $targetUrl = '/' . ltrim($targetUrl, '/');
                    } else {
                        // If no translation exists in target language, redirect to model's own actual URL (to avoid 404)
                        $targetUrl = $model->frontend_url;
                        $targetUrl = str_starts_with($targetUrl, 'http') ? parse_url($targetUrl, PHP_URL_PATH) : $targetUrl;
                        $targetUrl = '/' . ltrim($targetUrl, '/');
                    }
                    $redirectUrl = $queryString ? $targetUrl . '?' . $queryString : $targetUrl;
                    return redirect($redirectUrl);
                }
                // --- END SMART TRANSLATION ROUTING ---
                
                $targetPath = $langParam === $defaultLocale
                    ? '/' . $cleanPath
                    : '/' . $langParam . '/' . $cleanPath;
                    
                $targetPath = trim($targetPath, '/');
                $redirectUrl = $queryString ? '/' . $targetPath . '?' . $queryString : '/' . $targetPath;
                
                return redirect($redirectUrl);
            }
        }

        // 2. If no ?lang parameter, determine target locale
        $sessionLocale = $request->hasSession() ? session('locale') : null;

        // If user is logged in, and no session locale is set, load from database preference
        if ($isFrontend && !$sessionLocale) {
            $user = auth()->guard('web')->user() ?? auth()->guard('sanctum')->user() ?? auth()->user();
            if ($user) {
                $userLang = app(\App\Services\SettingsService::class)->get("user_language_{$user->id}");
                if ($userLang && (in_array($userLang, $activeLocales, true) || $userLang === $defaultLocale)) {
                    $sessionLocale = $userLang;
                    if ($request->hasSession()) {
                        session(['locale' => $userLang]);
                    }
                }
            }
        }

        if ($firstSegment && in_array($firstSegment, $activeLocales, true)) {
            App::setLocale($firstSegment);
            LanguageHelper::setCurrentLanguage($firstSegment);
            \Illuminate\Support\Facades\URL::defaults(['locale' => $firstSegment]);
            
            if ($request->hasSession() && $sessionLocale !== $firstSegment) {
                session(['locale' => $firstSegment]);
            }

            // Save user language preference if logged in on frontend
            if ($isFrontend) {
                $user = auth()->guard('web')->user() ?? auth()->guard('sanctum')->user() ?? auth()->user();
                if ($user) {
                    $userLangSetting = "user_language_{$user->id}";
                    $currentPreference = cache()->remember("user_pref_lang_{$user->id}", 300, function() use ($userLangSetting) {
                        return app(\App\Services\SettingsService::class)->get($userLangSetting);
                    });
                    if ($currentPreference !== $firstSegment) {
                        app(\App\Services\SettingsService::class)->set($userLangSetting, $firstSegment, 'user_preferences', 'string', false);
                        cache()->forget("user_pref_lang_{$user->id}");
                    }
                }
            }

            if ($request->route()) {
                $request->route()->forgetParameter('locale');
            }

            // --- SMART FALLBACK FOR UNTRANSLATED CONTENT IN PREFIXED ROUTE ---
            if ($isFrontend) {
                $segments = explode('/', $request->path());
                if (!empty($segments) && in_array($segments[0], $activeLocales, true)) {
                    array_shift($segments);
                }
                $cleanPath = implode('/', $segments);

                $model = $this->resolveModelForPath($cleanPath);
                if ($model && isset($model->translation_group_id) && $model->translation_group_id) {
                    $modelExistsInActiveLocale = false;
                    $targetUrl = null;

                    if ($model->locale === $firstSegment) {
                        $modelExistsInActiveLocale = true;
                    } else {
                        // Check if a translation exists in the active locale
                        $translation = $model->newQuery()
                            ->withoutGlobalScope('locale')
                            ->where('translation_group_id', $model->translation_group_id)
                            ->where('locale', $firstSegment)
                            ->first();

                        if ($translation) {
                            $modelExistsInActiveLocale = true;
                            
                            $normUrl = $translation->frontend_url;
                            if ($normUrl) {
                                $normUrlPath = str_starts_with((string) $normUrl, 'http') ? parse_url((string) $normUrl, PHP_URL_PATH) : (string) $normUrl;
                                $normUrlPath = ltrim($normUrlPath ?: '', '/');
                                
                                $urlSegs = explode('/', $normUrlPath);
                                if (!empty($urlSegs) && in_array($urlSegs[0], $activeLocales, true)) {
                                    array_shift($urlSegs);
                                }
                                $cleanTranslationPath = implode('/', $urlSegs);

                                if ($cleanPath !== $cleanTranslationPath) {
                                    $targetUrl = $translation->frontend_url;
                                }
                            }
                        }
                    }

                    if (!$modelExistsInActiveLocale) {
                        // The model does not exist in the active locale! Redirect to where it actually exists
                        $targetUrl = $model->frontend_url;
                    }

                    if ($targetUrl !== null) {
                        $targetUrl = str_starts_with((string) $targetUrl, 'http') ? parse_url((string) $targetUrl, PHP_URL_PATH) : (string) $targetUrl;
                        $targetUrl = '/' . ltrim((string) $targetUrl, '/');
                        
                        $query = $request->query();
                        $queryString = http_build_query($query);
                        $redirectUrl = $queryString ? $targetUrl . '?' . $queryString : $targetUrl;
                        
                        return redirect($redirectUrl);
                    }
                }
            }
            // --- END SMART FALLBACK ---
        } else {
            // Path is unprefixed (e.g. /blog)
            if ($isFrontend && $sessionLocale && in_array($sessionLocale, $activeLocales, true)) {
                $query = $request->query();
                $queryString = http_build_query($query);
                $cleanPath = ltrim($request->path(), '/');

                // --- SMART REDIRECT FOR PREFERRED LANGUAGE ---
                $model = $this->resolveModelForPath($cleanPath);
                if ($model && isset($model->translation_group_id) && $model->translation_group_id) {
                    // Check if translation exists in session preferred language
                    $translation = $model->newQuery()
                        ->withoutGlobalScope('locale')
                        ->where('translation_group_id', $model->translation_group_id)
                        ->where('locale', $sessionLocale)
                        ->first();

                    if ($translation && !empty($translation->frontend_url)) {
                        $targetUrl = $translation->frontend_url;
                        $targetUrl = str_starts_with((string) $targetUrl, 'http') ? parse_url((string) $targetUrl, PHP_URL_PATH) : (string) $targetUrl;
                        $targetUrl = '/' . ltrim((string) $targetUrl, '/');
                        
                        $redirectUrl = $queryString ? $targetUrl . '?' . $queryString : $targetUrl;
                        return redirect($redirectUrl);
                    } else {
                        // NO translation exists in the user's preferred language.
                        // So do NOT redirect them! Let them view the default/original language version.
                        App::setLocale($model->locale);
                        LanguageHelper::setCurrentLanguage($model->locale);
                        \Illuminate\Support\Facades\URL::defaults(['locale' => $model->locale]);
                        return $next($request);
                    }
                }
                // --- END SMART REDIRECT ---

                $targetPath = '/' . $sessionLocale . '/' . ltrim($request->path(), '/');
                $redirectUrl = $queryString ? $targetPath . '?' . $queryString : $targetPath;
                
                return redirect($redirectUrl);
            }

            // Exclude admin, api, installation, health checks, and static assets
            if ($isFrontend) {
                App::setLocale($defaultLocale);
                LanguageHelper::setCurrentLanguage($defaultLocale);
                \Illuminate\Support\Facades\URL::defaults(['locale' => $defaultLocale]);
                
                if ($request->hasSession() && !$sessionLocale) {
                    session(['locale' => $defaultLocale]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Resolve the translatable model instance matching a given clean request path.
     */
    protected function resolveModelForPath(string $cleanPath): ?\Illuminate\Database\Eloquent\Model
    {
        try {
            $segments = array_values(array_filter(explode('/', $cleanPath)));
            if (empty($segments)) {
                return null;
            }

            $settingsService = app(\App\Services\SettingsService::class);
            $permalinks = $settingsService->getPermalinkStructure();

            $postsSingleBase = trim($permalinks['posts']['single'] ?? 'posts', '/');
            $pagesBase = trim($permalinks['pages']['single'] ?? '', '/');
            $productsSingleBase = trim($permalinks['products']['single'] ?? 'products', '/');
            $categoryBase = trim($permalinks['categories']['single'] ?? 'categories', '/');
            $productCategoryBase = trim($permalinks['product_categories']['single'] ?? 'product-categories', '/');
            $productBrandBase = trim($permalinks['product_brands']['single'] ?? 'product-brands', '/');
            $postTagBase = trim($permalinks['tags']['post'] ?? 'tags', '/');
            $productTagBase = trim($permalinks['tags']['product'] ?? 'product-tags', '/');
            $projectCategoryBase = trim($permalinks['projects']['category'] ?? 'project-categories', '/');
            $projectSingleBase = trim($permalinks['projects']['single'] ?? 'projects', '/');

            $docsPrefix = get_option('flexidocs_doc_prefix', 'docs', 'theme_options');

            // Pattern 1: FlexiDocs Post show route: docs/{categorySlug}/{postSlug}
            if ($segments[0] === $docsPrefix && count($segments) === 3) {
                $postSlug = $segments[2];
                if (class_exists(\App\Models\Post::class) && Schema::hasTable('posts')) {
                    return \App\Models\Post::withoutGlobalScope('locale')
                        ->where('slug', $postSlug)
                        ->where('type', 'post')
                        ->first();
                }
            }

            // Pattern 2: FlexiDocs Category or standalone post route: docs/{slug}
            if ($segments[0] === $docsPrefix && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\Category::class) && Schema::hasTable('categories')) {
                    $category = \App\Models\Category::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                    if ($category) {
                        return $category;
                    }
                }
                if (class_exists(\App\Models\Post::class) && Schema::hasTable('posts')) {
                    return \App\Models\Post::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->where('type', 'post')
                        ->first();
                }
            }

            // Pattern 3: Posts show route: posts/{slug}
            if ($postsSingleBase !== '' && $segments[0] === $postsSingleBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\Post::class) && Schema::hasTable('posts')) {
                    return \App\Models\Post::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->where('type', 'post')
                        ->first();
                }
            }

            // Pattern 4: Products show route: products/{slug}
            if ($productsSingleBase !== '' && $segments[0] === $productsSingleBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\Product::class) && Schema::hasTable('products')) {
                    return \App\Models\Product::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            }

            // Pattern 5: Categories show route: categories/{slug}
            if ($categoryBase !== '' && $segments[0] === $categoryBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\Category::class) && Schema::hasTable('categories')) {
                    return \App\Models\Category::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            }

            // Pattern 6: Product categories show route: product-categories/{slug}
            if ($productCategoryBase !== '' && $segments[0] === $productCategoryBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\ProductCategory::class) && Schema::hasTable('product_categories')) {
                    return \App\Models\ProductCategory::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            }

            // Pattern 6a: Project categories show route: project-categories/{slug}
            $isProjectHubActive = false;
            try {
                $isProjectHubActive = app(\App\Services\ModuleManager::class)->isModuleEnabled('Polyx.ProjectHub');
            } catch (\Exception $e) {
            }

            if ($isProjectHubActive) {
                if ($projectCategoryBase !== '' && $segments[0] === $projectCategoryBase && count($segments) === 2) {
                    $slug = $segments[1];
                    if (class_exists(\App\Models\Category::class) && Schema::hasTable('categories')) {
                        return \App\Models\Category::withoutGlobalScope('locale')
                            ->where('type', 'project')
                            ->where('slug', $slug)
                            ->first();
                    }
                }

                // Pattern 6b: Projects show route: projects/{slug}
                if ($projectSingleBase !== '' && $segments[0] === $projectSingleBase && count($segments) === 2) {
                    $slug = $segments[1];
                    if (class_exists(\Modules\Polyx\ProjectHub\Models\Project::class) && Schema::hasTable('projects')) {
                        return \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')
                            ->where('slug', $slug)
                            ->first();
                    }
                }
            }

            // Pattern 7: Product brands show route: product-brands/{slug}
            if ($productBrandBase !== '' && $segments[0] === $productBrandBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\ProductBrand::class) && Schema::hasTable('product_brands')) {
                    return \App\Models\ProductBrand::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            }

            // Pattern 8: Post Tags show route: tags/{slug}
            if ($postTagBase !== '' && $segments[0] === $postTagBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\PostTag::class) && Schema::hasTable('post_tags')) {
                    return \App\Models\PostTag::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            }

            // Pattern 9: Product Tags show route: product-tags/{slug}
            if ($productTagBase !== '' && $segments[0] === $productTagBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\ProductTag::class) && Schema::hasTable('product_tags')) {
                    return \App\Models\ProductTag::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            }

            // Pattern 10: Page show route or fallback
            if ($pagesBase !== '' && $segments[0] === $pagesBase && count($segments) === 2) {
                $slug = $segments[1];
                if (class_exists(\App\Models\Post::class) && Schema::hasTable('posts')) {
                    return \App\Models\Post::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->where('type', 'page')
                        ->first();
                }
            }

            if ($categoryBase === '' && count($segments) === 1) {
                $slug = $segments[0];
                if (class_exists(\App\Models\Category::class) && Schema::hasTable('categories')) {
                    $category = \App\Models\Category::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                    if ($category) {
                        return $category;
                    }
                }
            }

            if ($pagesBase === '' && count($segments) === 1) {
                $slug = $segments[0];
                if (class_exists(\App\Models\Post::class) && Schema::hasTable('posts')) {
                    return \App\Models\Post::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions
        }

        return null;
    }
}
