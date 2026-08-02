<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\FrontendController;
use App\Models\Product;
use App\Services\SettingsService;
use App\Services\TemplateResolver;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProductController extends FrontendController
{
    public function __construct(
        protected SettingsService $settingsService,
        protected TemplateResolver $templateResolver,
    ) {}

    /**
     * Display a listing of products
     */
    public function index(Request $request): View
    {
        $query = Product::with(['categories', 'tags', 'media'])
            ->where('status', 'published');

        // Hide test products for non-admin users
        $isAdmin = $this->isAdmin($request);
        if (!$isAdmin && !app()->runningUnitTests()) {
            $query->where('slug', 'not like', 'test-%');
        }

        // Category filter
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->get('category'));
            });
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description_html', 'like', "%{$search}%");
            });
        }

        // Featured filter
        if ($request->has('featured')) {
            $query->where('featured', true);
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 12), 50);
        $products = $query->paginate($perPage)->withQueryString();

        $data = [
            'products' => $products,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'products.index');

        $viewName = $this->templateResolver->resolve('products.index', null, 'products');
        $data['__templateTheme'] = null;
        return view($viewName, $data);
    }

    /**
     * Display a single product
     */
    public function show(string $slug, Request $request)
    {
        $query = Product::with(['user', 'categories', 'tags', 'media', 'services', 'variantAttributes.values', 'variantAttributes.group', 'activeVariants.image'])
            ->where('slug', $slug);

        // Check if user is admin - allow viewing draft products
        $isAdmin = $this->isAdmin($request);

        if (!$isAdmin) {
            // Non-admin users can only see published products
            $query->where('status', 'published');
        }
        // Admin users can see all products (draft, published, archived)

        $product = $query->first();

        // FALLBACK: If product translation does not exist in current locale, fallback to primary language version of product directly
        if (!$product) {
            $fallbackQuery = Product::withoutGlobalScope('locale')
                ->with(['user', 'categories', 'tags', 'media', 'services', 'variantAttributes.values', 'variantAttributes.group', 'activeVariants.image'])
                ->where('slug', $slug);

            if (!$isAdmin) {
                $fallbackQuery->where('status', 'published');
            }

            $product = $fallbackQuery->first();

            if (!$product) {
                abort(404);
            }
        }

        // Enforce: test products are only visible to system admins
        if (str_starts_with($product->slug, 'test-') && !$isAdmin && !app()->runningUnitTests()) {
            abort(404);
        }

        // Only increment views for published products or when admin is viewing (ignore prefetch requests)
        if (($product->status === 'published' || $isAdmin) && !is_prefetch_request()) {
            $trackingMode = \App\Support\OptionRepository::get('product_views_tracking_mode', 'every_visit');
            if ($trackingMode === 'unique_24h') {
                $cookieName = 'viewed_product_' . $product->id;
                if (!$request->hasCookie($cookieName)) {
                    $product->increment('views');
                    cookie()->queue($cookieName, '1', 1440); // 1440 minutes = 24 hours
                }
            } else {
                $product->increment('views');
            }
        }

        $salesCount = 0;
        if (Schema::hasTable('order_items') && Schema::hasTable('orders')) {
            $salesCount = (int) DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.product_id', $product->id)
                ->whereIn('orders.status', ['processing', 'completed'])
                ->sum('order_items.quantity');
        }

        $productVersion = data_get($product->settings, 'version')
            ?? data_get($product->settings, 'product_version')
            ?? data_get($product->settings, 'module_version')
            ?? null;

        if (!$productVersion && !empty($product->translation_group_id)) {
            $defaultLocale = config('app.locale', 'en');
            $primaryProduct = \App\Models\Product::withoutGlobalScope('locale')
                ->where('translation_group_id', $product->translation_group_id)
                ->where('locale', $defaultLocale)
                ->first();
            if ($primaryProduct) {
                $productVersion = data_get($primaryProduct->settings, 'version')
                    ?? data_get($primaryProduct->settings, 'product_version')
                    ?? data_get($primaryProduct->settings, 'module_version')
                    ?? null;
            }
        }

        [$productFaqItems, $hasProductFaqTab] = $this->resolveProductFaqItems($product);
        [$productCustomTabs, $hasProductCustomTabs, $defaultProductCustomTabId] = $this->resolveProductCustomTabs($product);

        $projectReleases = collect();
        $projectFeatures = collect();
        $projectUrl = null;
        $project = null;

        $isProjectHubActive = false;
        try {
            $isProjectHubActive = app(\App\Services\ModuleManager::class)->isModuleEnabled('Polyx.ProjectHub');
        } catch (\Exception $e) {
        }

        if ($isProjectHubActive && class_exists(\Modules\Polyx\ProjectHub\Models\Project::class)) {
            $productIds = [$product->id];
            if (!empty($product->translation_group_id)) {
                $groupProductIds = \App\Models\Product::withoutGlobalScope('locale')
                    ->where('translation_group_id', $product->translation_group_id)
                    ->pluck('id')
                    ->toArray();
                $productIds = array_merge($productIds, $groupProductIds);
            }
            if (!empty($product->slug)) {
                $slugProductIds = \App\Models\Product::withoutGlobalScope('locale')
                    ->where('slug', $product->slug)
                    ->pluck('id')
                    ->toArray();
                $productIds = array_merge($productIds, $slugProductIds);
            }
            $productIds = array_unique(array_filter($productIds));

            $mainProject = \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')
                ->whereHas('products', function ($q) use ($productIds) {
                    $q->withoutGlobalScope('locale')->whereIn('products.id', $productIds);
                })
                ->where('status', 'published')
                ->first();

            if ($mainProject) {
                $project = $mainProject;
                $currentLocale = \Illuminate\Support\Facades\App::getLocale() ?: 'en';
                if ($mainProject->locale !== $currentLocale && !empty($mainProject->translation_group_id)) {
                    $translatedProject = \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')
                        ->where('translation_group_id', $mainProject->translation_group_id)
                        ->where('locale', $currentLocale)
                        ->where('status', 'published')
                        ->first();
                    if ($translatedProject) {
                        $project = $translatedProject;
                    }
                }
            }
        }

        if ($project) {
            $rawReleases = \Modules\Polyx\ProjectHub\Models\ProjectRelease::where('project_id', $project->id)
                ->where('status', 'published')
                ->orderByDesc('released_at')
                ->orderByDesc('id')
                ->take(5)
                ->get();

            if ($rawReleases->isEmpty() && isset($mainProject) && $mainProject && $mainProject->id !== $project->id) {
                $rawReleases = \Modules\Polyx\ProjectHub\Models\ProjectRelease::where('project_id', $mainProject->id)
                    ->where('status', 'published')
                    ->orderByDesc('released_at')
                    ->orderByDesc('id')
                    ->take(5)
                    ->get();
            }

            $projectReleases = $rawReleases->map(function ($release) {
                return [
                    'id' => $release->id,
                    'version' => $release->version,
                    'title' => \Modules\Polyx\ProjectHub\Models\ProjectRelease::stripEmojis((string) $release->title, true),
                    'summary' => \Modules\Polyx\ProjectHub\Models\ProjectRelease::stripEmojis((string) $release->summary),
                    'release_notes_html' => \Modules\Polyx\ProjectHub\Models\ProjectRelease::stripEmojis((string) $release->release_notes_html),
                    'released_at' => optional($release->released_at)->format('d/m/Y'),
                ];
            });

            if (!$productVersion && $rawReleases->isNotEmpty()) {
                $productVersion = $rawReleases->first()['version'];
            }

            $rawFeatures = \Modules\Polyx\ProjectHub\Models\ProjectFeature::where('project_id', $project->id)
                ->whereIn('status', ['in_progress', 'backlog'])
                ->orderBy('order')
                ->get();

            if ($rawFeatures->isEmpty() && isset($mainProject) && $mainProject && $mainProject->id !== $project->id) {
                $rawFeatures = \Modules\Polyx\ProjectHub\Models\ProjectFeature::where('project_id', $mainProject->id)
                    ->whereIn('status', ['in_progress', 'backlog'])
                    ->orderBy('order')
                    ->get();
            }

            $projectFeatures = $rawFeatures->map(function ($feature) {
                return [
                    'title' => \Modules\Polyx\ProjectHub\Models\ProjectRelease::stripEmojis((string) $feature->title, true),
                    'description' => \Modules\Polyx\ProjectHub\Models\ProjectRelease::stripEmojis((string) $feature->description),
                    'status' => $feature->status,
                    'priority' => $feature->priority,
                    'added_at' => $feature->added_at ? $feature->added_at->format('Y-m-d') : ($feature->created_at ? $feature->created_at->format('Y-m-d') : null),
                ];
            });

            try {
                $locale = \App\Helpers\LanguageHelper::getCurrentLanguage() ?: 'en';
                $projectUrl = route('projects.show.page', ['locale' => $locale, 'slug' => $project->slug]);
            } catch (\Throwable $e) {
                try {
                    $projectUrl = route('projects.show.page', ['slug' => $project->slug]);
                } catch (\Throwable $ex) {
                    $projectUrl = null;
                }
            }
        }

        $data = [
            'product' => $product,
            'productSalesCount' => $salesCount,
            'productVersion' => $productVersion,
            'productFaqItems' => $productFaqItems,
            'hasProductFaqTab' => $hasProductFaqTab,
            'productCustomTabs' => $productCustomTabs,
            'hasProductCustomTabs' => $hasProductCustomTabs,
            'defaultProductCustomTabId' => $defaultProductCustomTabId,
            'projectReleases' => $projectReleases,
            'projectFeatures' => $projectFeatures,
            'projectUrl' => $projectUrl,
            'project' => $project,
        ];

        // Apply theme filter
        $data = \App\Facades\Hook::applyFilters('theme.view.data', $data, 'products.show');

        $templateTheme = $product->template_theme ?? null;
        $viewName = $this->templateResolver->resolve('products.show', $templateTheme, 'products');
        $data['__templateTheme'] = $templateTheme;
        return view($viewName, $data);
    }

    /**
     * Preview product live demo via iframe page
     */
    public function preview(string $slug, Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $query = Product::with(['media', 'services'])
            ->where('slug', $slug);

        $isAdmin = $this->isAdmin($request);
        if (!$isAdmin) {
            $query->where('status', 'published');
        }

        $product = $query->firstOrFail();

        $demoUrl = trim((string) data_get($product->settings, 'demo_url', ''));
        if (empty($demoUrl)) {
            abort(404, 'Demo URL not configured for this product.');
        }

        $previewDirect = (bool) data_get($product->settings, 'preview_direct', true);
        if ($previewDirect) {
            return redirect($demoUrl);
        }

        $defaultPrice = ($product->sale_price && $product->sale_price < $product->price) ? $product->sale_price : $product->price;
        $isDigitalProduct = ($product->type ?? 'product') === 'digital';
        $hasPackages = $isDigitalProduct && $product->services && $product->services->count() > 0;

        $defaultCartItem = [
            'product_id' => $product->id,
            'service_id' => null,
            'variant_id' => null,
            'name' => $product->name,
            'price' => (float) $defaultPrice,
            'quantity' => 1,
            'billing_cycle' => 'month',
            'image_url' => $product->primaryImage()?->url,
            'slug' => $product->slug,
            'permalink' => $product->frontend_url
        ];

        $data = [
            'product' => $product,
            'demoUrl' => $demoUrl,
            'defaultCartItem' => $defaultCartItem,
            'hasPackages' => $hasPackages,
        ];

        return view('products.preview', $data);
    }

    /**
     * @return array{0: Collection<int, array{id: string, question: string, answer: string, open: bool}>, 1: bool}
     */
    protected function resolveProductFaqItems(Product $product): array
    {
        $faqConfig = data_get($product->settings, 'faq', []);
        $faqEnabled = (bool) data_get($faqConfig, 'enabled', false);
        $source = (string) data_get($faqConfig, 'source', 'none');

        if (!$faqEnabled || $source === 'none') {
            return [collect(), false];
        }

        $resolved = collect();

        if (in_array($source, ['global', 'global_custom'], true)) {
            $globalFaqEnabled = (bool) $this->settingsService->get('global_faqs_enabled', true);
            if ($globalFaqEnabled) {
                $globalItems = collect($this->settingsService->get('global_faqs_items', []))
                    ->map(fn ($item, $index) => $this->normalizeFaqItem($item, "global-{$index}"))
                    ->filter(fn ($item) => !empty($item['question']) && !empty($item['answer']))
                    ->values();

                $globalMode = (string) data_get($faqConfig, 'global_mode', 'all');
                $selectedIds = collect(data_get($faqConfig, 'global_ids', []))->map(fn ($id) => (string) $id)->all();

                if ($globalMode === 'selected') {
                    if (empty($selectedIds)) {
                        $globalItems = collect();
                    } else {
                        $globalItems = $globalItems->filter(fn ($item) => in_array((string) $item['id'], $selectedIds, true))->values();
                    }
                }

                $expandAll = (bool) $this->settingsService->get('global_faqs_expand_all', false);
                $globalItems = $globalItems->map(function ($item) use ($expandAll) {
                    $item['open'] = $expandAll || !empty($item['open']);
                    return $item;
                });

                $resolved = $resolved->concat($globalItems);
            }
        }

        if (in_array($source, ['custom', 'global_custom'], true)) {
            $customItems = collect(data_get($faqConfig, 'custom_items', []))
                ->map(fn ($item, $index) => $this->normalizeFaqItem($item, "custom-{$index}"))
                ->filter(fn ($item) => !empty($item['question']) && !empty($item['answer']))
                ->values();

            $resolved = $resolved->concat($customItems);
        }

        return [$resolved->values(), $resolved->isNotEmpty()];
    }

    /**
     * @param mixed $item
     * @return array{id: string, question: string, answer: string, open: bool}
     */
    protected function normalizeFaqItem(mixed $item, string $fallbackId): array
    {
        return [
            'id' => (string) data_get($item, 'id', $fallbackId),
            'question' => (string) data_get($item, 'question', ''),
            'answer' => (string) data_get($item, 'answer', ''),
            'open' => (bool) data_get($item, 'open', false),
        ];
    }

    /**
     * @return array{0: Collection<int, array{id: string, title: string, content: string, active_default: bool}>, 1: bool, 2: string|null}
     */
    protected function resolveProductCustomTabs(Product $product): array
    {
        $tabConfig = data_get($product->settings, 'tabs', []);
        $tabsEnabled = (bool) data_get($tabConfig, 'enabled', false);
        $source = (string) data_get($tabConfig, 'source', 'none');

        if (!$tabsEnabled || $source === 'none') {
            return [collect(), false, null];
        }

        $resolved = collect();

        if (in_array($source, ['global', 'global_custom'], true)) {
            $globalTabsEnabled = (bool) $this->settingsService->get('global_tabs_enabled', true);
            if ($globalTabsEnabled) {
                $globalItems = collect($this->settingsService->get('global_tabs_items', []))
                    ->map(fn ($item, $index) => $this->normalizeTabItem($item, "global-tab-{$index}"))
                    ->filter(fn ($item) => !empty($item['title']) && !empty($item['content']))
                    ->values();

                $globalMode = (string) data_get($tabConfig, 'global_mode', 'all');
                $selectedIds = collect(data_get($tabConfig, 'global_ids', []))->map(fn ($id) => (string) $id)->all();

                if ($globalMode === 'selected') {
                    if (empty($selectedIds)) {
                        $globalItems = collect();
                    } else {
                        $globalItems = $globalItems->filter(fn ($item) => in_array((string) $item['id'], $selectedIds, true))->values();
                    }
                }

                $resolved = $resolved->concat($globalItems);
            }
        }

        if (in_array($source, ['custom', 'global_custom'], true)) {
            $customItems = collect(data_get($tabConfig, 'custom_items', []))
                ->map(fn ($item, $index) => $this->normalizeTabItem($item, "custom-tab-{$index}"))
                ->filter(fn ($item) => !empty($item['title']) && !empty($item['content']))
                ->values();

            $resolved = $resolved->concat($customItems);
        }

        $resolved = $resolved->values();
        $configuredDefaultTabId = (string) data_get($tabConfig, 'default_tab_id', '');
        $defaultTabId = null;

        if ($configuredDefaultTabId !== '') {
            $exists = in_array($configuredDefaultTabId, ['description', 'faqs'], true)
                || $resolved->contains(fn ($tab) => (string) ($tab['id'] ?? '') === $configuredDefaultTabId);
            if ($exists) {
                $defaultTabId = $configuredDefaultTabId;
            }
        }

        if ($defaultTabId === null) {
            $defaultTabId = optional($resolved->firstWhere('active_default', true))['id'] ?? null;
        }

        return [$resolved, $resolved->isNotEmpty(), $defaultTabId];
    }

    /**
     * @param mixed $item
     * @return array{id: string, title: string, content: string, active_default: bool}
     */
    protected function normalizeTabItem(mixed $item, string $fallbackId): array
    {
        return [
            'id' => (string) data_get($item, 'id', $fallbackId),
            'title' => (string) data_get($item, 'title', ''),
            'content' => (string) data_get($item, 'content', ''),
            'active_default' => (bool) data_get($item, 'active_default', false),
        ];
    }
}
