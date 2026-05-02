<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Support;

use Illuminate\Support\Facades\Log;

class SchemaPieceRegistry
{
    /**
     * @var array<string, array{factory: callable, priority: int}>
     */
    protected array $pieces = [];

    public function register(string $key, callable $factory, int $priority = 10): void
    {
        $this->pieces[$key] = [
            'factory' => $factory,
            'priority' => $priority,
        ];
    }

    public function remove(string $key): void
    {
        unset($this->pieces[$key]);
    }

    /**
     * @return array<string, array{factory: callable, priority: int}>
     */
    public function all(): array
    {
        uasort($this->pieces, static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        return $this->pieces;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function resolve(array $context, array $payload = []): array
    {
        $resolved = [];
        $pieces = MTOptimizeHooks::applyFilters('mtoptimize/schema/pieces', $this->all(), $context, $payload);

        foreach ($pieces as $key => $piece) {
            $factory = is_array($piece) ? ($piece['factory'] ?? null) : null;

            if (!is_callable($factory)) {
                continue;
            }

            try {
                $node = $factory($context, $payload);
            } catch (\Throwable $exception) {
                Log::warning('[MTOptimize] Schema piece failed', [
                    'piece' => (string) $key,
                    'message' => $exception->getMessage(),
                ]);
                continue;
            }

            if ($node === null) {
                continue;
            }

            if (is_array($node) && array_is_list($node)) {
                foreach ($node as $item) {
                    if (is_array($item)) {
                        $resolved[] = MTOptimizeHooks::applyFilters('mtoptimize/schema/piece', $item, $context, $payload, $key);
                    }
                }
                continue;
            }

            if (is_array($node)) {
                $resolved[] = MTOptimizeHooks::applyFilters('mtoptimize/schema/piece', $node, $context, $payload, $key);
            }
        }

        return $resolved;
    }
}
