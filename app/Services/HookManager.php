<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Hook Manager - WordPress-like hooks and filters system
 * 
 * Supports actions (void callbacks) and filters (value transformers)
 * with priority-based execution order.
 */
class HookManager
{
    /**
     * Registered action callbacks
     * 
     * @var array<string, array<int, array{callback: callable, priority: int}>>
     */
    protected array $actions = [];

    /**
     * Registered filter callbacks
     * 
     * @var array<string, array<int, array{callback: callable, priority: int}>>
     */
    protected array $filters = [];

    /**
     * Add an action hook
     * 
     * @param string $hookName The name of the action hook
     * @param callable|string $callback The callback function or method
     * @param int $priority Priority (lower number = earlier execution, default 10)
     * @return void
     */
    public function addAction(string $hookName, callable|string $callback, int $priority = 10): void
    {
        if (!isset($this->actions[$hookName])) {
            $this->actions[$hookName] = [];
        }

        $this->actions[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort($this->actions[$hookName], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Execute an action hook
     * 
     * @param string $hookName The name of the action hook
     * @param mixed ...$args Arguments to pass to callbacks
     * @return void
     */
    public function doAction(string $hookName, mixed ...$args): void
    {
        if (!isset($this->actions[$hookName])) {
            return;
        }

        foreach ($this->actions[$hookName] as $hook) {
            $this->callCallback($hook['callback'], $args);
        }
    }

    /**
     * Add a filter hook
     * 
     * @param string $hookName The name of the filter hook
     * @param callable|string $callback The callback function or method
     * @param int $priority Priority (lower number = earlier execution, default 10)
     * @return void
     */
    public function addFilter(string $hookName, callable|string $callback, int $priority = 10): void
    {
        if (!isset($this->filters[$hookName])) {
            $this->filters[$hookName] = [];
        }

        $this->filters[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort($this->filters[$hookName], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Apply filter hooks to a value
     * 
     * @param string $hookName The name of the filter hook
     * @param mixed $value The value to filter
     * @param mixed ...$args Additional arguments to pass to callbacks
     * @return mixed The filtered value
     */
    public function applyFilters(string $hookName, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hookName])) {
            return $value;
        }

        $filtered = $value;

        foreach ($this->filters[$hookName] as $hook) {
            $filtered = $this->callCallback($hook['callback'], [$filtered, ...$args]);
        }

        return $filtered;
    }

    /**
     * Remove an action hook
     * 
     * @param string $hookName The name of the action hook
     * @param callable|string|null $callback The callback to remove (null removes all)
     * @return bool True if removed, false otherwise
     */
    public function removeAction(string $hookName, callable|string|null $callback = null): bool
    {
        if (!isset($this->actions[$hookName])) {
            return false;
        }

        if ($callback === null) {
            unset($this->actions[$hookName]);
            return true;
        }

        $this->actions[$hookName] = array_filter(
            $this->actions[$hookName],
            fn($hook) => $hook['callback'] !== $callback
        );

        if (empty($this->actions[$hookName])) {
            unset($this->actions[$hookName]);
        }

        return true;
    }

    /**
     * Remove a filter hook
     * 
     * @param string $hookName The name of the filter hook
     * @param callable|string|null $callback The callback to remove (null removes all)
     * @return bool True if removed, false otherwise
     */
    public function removeFilter(string $hookName, callable|string|null $callback = null): bool
    {
        if (!isset($this->filters[$hookName])) {
            return false;
        }

        if ($callback === null) {
            unset($this->filters[$hookName]);
            return true;
        }

        $this->filters[$hookName] = array_filter(
            $this->filters[$hookName],
            fn($hook) => $hook['callback'] !== $callback
        );

        if (empty($this->filters[$hookName])) {
            unset($this->filters[$hookName]);
        }

        return true;
    }

    /**
     * Check if an action hook has callbacks
     * 
     * @param string $hookName The name of the action hook
     * @return bool
     */
    public function hasAction(string $hookName): bool
    {
        return isset($this->actions[$hookName]) && !empty($this->actions[$hookName]);
    }

    /**
     * Check if a filter hook has callbacks
     * 
     * @param string $hookName The name of the filter hook
     * @return bool
     */
    public function hasFilter(string $hookName): bool
    {
        return isset($this->filters[$hookName]) && !empty($this->filters[$hookName]);
    }

    /**
     * Call a callback function or method
     * 
     * @param callable|string $callback
     * @param array $args
     * @return mixed
     */
    protected function callCallback(callable|string $callback, array $args): mixed
    {
        if (is_string($callback) && str_contains($callback, '::')) {
            [$class, $method] = explode('::', $callback, 2);
            $callback = [app($class), $method];
        }

        return call_user_func_array($callback, $args);
    }
}
