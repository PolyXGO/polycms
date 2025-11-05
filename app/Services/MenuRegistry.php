<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Menu Registry - Collects admin menu items from modules
 */
class MenuRegistry
{
    protected array $menuItems = [];

    /**
     * Register a menu item
     * 
     * @param string $key Unique key for the menu item
     * @param array $item Menu item data
     * @return void
     */
    public function register(string $key, array $item): void
    {
        $this->menuItems[$key] = $item;
    }

    /**
     * Register multiple menu items
     * 
     * @param array<string, array> $items
     * @return void
     */
    public function registerMany(array $items): void
    {
        foreach ($items as $key => $item) {
            $this->register($key, $item);
        }
    }

    /**
     * Get all registered menu items
     * 
     * @return array<string, array>
     */
    public function all(): array
    {
        // Sort by order if present
        uasort($this->menuItems, function ($a, $b) {
            $orderA = $a['order'] ?? 999;
            $orderB = $b['order'] ?? 999;
            return $orderA <=> $orderB;
        });

        return $this->menuItems;
    }

    /**
     * Clear all menu items
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->menuItems = [];
    }
}
