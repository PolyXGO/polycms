<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Account Menu Registry — Collects customer account sidebar menu items from modules.
 *
 * Modules use the `account.menu.build` hook to register menu items:
 *
 *   Hook::addAction('account.menu.build', function () {
 *       app(AccountMenuRegistry::class)->register('my-module', [
 *           'label' => 'API Keys',
 *           'route' => '/account/api',
 *           'icon'  => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
 *           'order' => 50,
 *       ]);
 *   }, 20);
 */
class AccountMenuRegistry
{
    protected array $items = [];

    /**
     * Register a menu item for the customer account sidebar.
     *
     * @param string $key Unique key for the menu item
     * @param array  $item Menu item data (label, route, icon, order)
     */
    public function register(string $key, array $item): void
    {
        $this->items[$key] = $item;
    }

    /**
     * Get all registered account menu items, sorted by order.
     *
     * @return array<string, array>
     */
    public function all(): array
    {
        uasort($this->items, function ($a, $b) {
            return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
        });

        return $this->items;
    }

    /**
     * Check if any items have been registered.
     */
    public function hasItems(): bool
    {
        return !empty($this->items);
    }

    /**
     * Clear all items.
     */
    public function clear(): void
    {
        $this->items = [];
    }
}
