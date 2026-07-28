<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Support;

class SitemapProviderRegistry
{
    /**
     * @var array<string, array{callback: callable, priority: int}>
     */
    protected array $providers = [];

    public function register(string $type, callable $callback, int $priority = 10): void
    {
        $key = trim($type);

        if ($key === '') {
            return;
        }

        $this->providers[$key] = [
            'callback' => $callback,
            'priority' => $priority,
        ];
    }

    public function remove(string $type): void
    {
        unset($this->providers[$type]);
    }

    /**
     * @return array<string, array{callback: callable, priority: int}>
     */
    public function all(): array
    {
        uasort($this->providers, static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        $providers = MTOptimizeHooks::applyFilters('mtoptimize/sitemap/providers', $this->providers);

        return is_array($providers) ? $providers : $this->providers;
    }

    public function has(string $type): bool
    {
        return isset($this->providers[$type]);
    }

    /**
     * @return array{callback: callable, priority: int}|null
     */
    public function get(string $type): ?array
    {
        return $this->providers[$type] ?? null;
    }
}
