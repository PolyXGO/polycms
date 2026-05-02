<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Support;

use App\Services\SettingsService;

class TemplateVariableEngine
{
    /**
     * @var array<string, callable>
     */
    protected array $variables = [];

    public function __construct(
        protected SettingsService $settingsService,
    ) {
        $this->registerDefaults();
    }

    public function registerVariable(string $key, callable $resolver): void
    {
        $normalized = trim($key);
        if ($normalized === '') {
            return;
        }

        $this->variables[$normalized] = $resolver;
    }

    public function resolveVariables(array $context, array $extra = []): array
    {
        $entity = $context['entity'] ?? null;
        $primaryTaxonomy = is_array($context['primaryTaxonomy'] ?? null) ? $context['primaryTaxonomy'] : [];
        $page = max(1, (int) ($context['pagination']['page'] ?? 1));

        $vars = [
            'siteName' => (string) $this->settingsService->get('site_title', config('app.name', 'PolyCMS')),
            'siteTagline' => (string) $this->settingsService->get('tagline', ''),
            'title' => (string) ($extra['title'] ?? $this->extractEntityTitle($entity)),
            'pageTitle' => (string) ($extra['pageTitle'] ?? $this->extractEntityTitle($entity)),
            'excerpt' => (string) ($extra['excerpt'] ?? $this->extractEntityExcerpt($entity)),
            'description' => (string) ($extra['description'] ?? $this->extractEntityExcerpt($entity)),
            'locale' => (string) ($context['locale'] ?? app()->getLocale()),
            'routeName' => (string) ($context['routeName'] ?? ''),
            'entityType' => (string) ($context['entityType'] ?? ''),
            'primaryCategory' => (string) ($primaryTaxonomy['name'] ?? ''),
            'primaryCategoryName' => (string) ($primaryTaxonomy['name'] ?? ''),
            'primaryCategoryUrl' => (string) ($primaryTaxonomy['url'] ?? ''),
            'page' => (string) $page,
            'currentYear' => (string) now()->format('Y'),
        ];

        foreach ($this->variables as $key => $resolver) {
            try {
                $vars[$key] = (string) $resolver($context, $extra, $vars);
            } catch (\Throwable) {
                // Keep resolution resilient for third-party variable resolvers.
            }
        }

        $vars = array_merge($vars, array_map(static fn ($value): string => (string) $value, $extra));

        return MTOptimizeHooks::applyFilters('mtoptimize/template/variables', $vars, $context, $extra);
    }

    public function render(string $template, array $context, array $extra = []): string
    {
        $template = trim($template);
        if ($template === '') {
            return '';
        }

        $variables = $this->resolveVariables($context, $extra);

        $resolved = preg_replace_callback('/\{([a-zA-Z0-9_\-]+)\}/', function (array $matches) use ($variables): string {
            $key = (string) ($matches[1] ?? '');
            return $variables[$key] ?? $matches[0];
        }, $template) ?? $template;

        $removeUnknown = (bool) $this->settingsService->get('mtoptimize_template_remove_unknown', true);
        if ($removeUnknown) {
            $resolved = (string) preg_replace('/\{[a-zA-Z0-9_\-]+\}/', '', $resolved);
        }

        $resolved = trim(preg_replace('/\s+/', ' ', $resolved) ?? $resolved);

        return MTOptimizeHooks::applyFilters('mtoptimize/template/render', $resolved, $template, $context, $variables);
    }

    protected function registerDefaults(): void
    {
        $this->registerVariable('requestPath', static fn (array $context): string => (string) ($context['requestPath'] ?? ''));
        $this->registerVariable('fullUrl', static fn (array $context): string => (string) ($context['fullUrl'] ?? ''));
    }

    protected function extractEntityTitle(mixed $entity): string
    {
        if (!is_object($entity)) {
            return '';
        }

        return (string) (
            $entity->title
            ?? $entity->name
            ?? $entity->slug
            ?? ''
        );
    }

    protected function extractEntityExcerpt(mixed $entity): string
    {
        if (!is_object($entity)) {
            return '';
        }

        $text = (string) (
            $entity->excerpt
            ?? $entity->summary
            ?? $entity->short_description
            ?? $entity->description
            ?? $entity->content_html
            ?? ''
        );

        if ($text === '') {
            return '';
        }

        $text = strip_tags($text);

        if (mb_strlen($text) <= 180) {
            return $text;
        }

        return trim(mb_substr($text, 0, 177)) . '...';
    }
}
