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
     * Registered action callbacks.
     *
     * @var array<string, array<int, array<int, array{callback: callable|string, accepted_args: int}>>>
     */
    protected array $actions = [];

    /**
     * Registered filter callbacks.
     *
     * @var array<string, array<int, array<int, array{callback: callable|string, accepted_args: int}>>>
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
    public function addAction(string $hookName, callable|string $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->addHook($this->actions, $hookName, $callback, $priority, $acceptedArgs);
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

        ksort($this->actions[$hookName]);

        foreach ($this->actions[$hookName] as $hooks) {
            foreach ($hooks as $hook) {
                $callbackArgs = array_slice($args, 0, $hook['accepted_args']);
                $this->callCallback($hook['callback'], $callbackArgs);
            }
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
    public function addFilter(string $hookName, callable|string $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->addHook($this->filters, $hookName, $callback, $priority, $acceptedArgs);
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

        ksort($this->filters[$hookName]);

        $filtered = $value;
        $arguments = array_merge([$filtered], $args);

        foreach ($this->filters[$hookName] as $hooks) {
            foreach ($hooks as $hook) {
                $callbackArgs = array_slice($arguments, 0, $hook['accepted_args']);
                $filtered = $this->callCallback($hook['callback'], $callbackArgs);
                $arguments[0] = $filtered;
            }
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
    public function removeAction(string $hookName, callable|string|null $callback = null, ?int $priority = null): bool
    {
        return $this->removeHook($this->actions, $hookName, $callback, $priority);
    }

    /**
     * Remove a filter hook
     * 
     * @param string $hookName The name of the filter hook
     * @param callable|string|null $callback The callback to remove (null removes all)
     * @return bool True if removed, false otherwise
     */
    public function removeFilter(string $hookName, callable|string|null $callback = null, ?int $priority = null): bool
    {
        return $this->removeHook($this->filters, $hookName, $callback, $priority);
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
        } elseif (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);
            $callback = [app($class), $method];
        }

        return call_user_func_array($callback, $args);
    }

    /**
     * Store a hook definition.
     *
     * @param  array<string, array<int, array<int, array{callback: callable|string, accepted_args: int}>>>  $registry
     */
    protected function addHook(array &$registry, string $hookName, callable|string $callback, int $priority, int $acceptedArgs): void
    {
        if (!isset($registry[$hookName][$priority])) {
            $registry[$hookName][$priority] = [];
        }

        $registry[$hookName][$priority][] = [
            'callback' => $callback,
            'accepted_args' => max(0, $acceptedArgs),
        ];
    }

    /**
     * Remove hook definitions.
     *
     * @param  array<string, array<int, array<int, array{callback: callable|string, accepted_args: int}>>>  $registry
     */
    protected function removeHook(array &$registry, string $hookName, callable|string|null $callback = null, ?int $priority = null): bool
    {
        if (!isset($registry[$hookName])) {
            return false;
        }

        if ($callback === null && $priority === null) {
            unset($registry[$hookName]);
            return true;
        }

        if ($priority !== null) {
            if (!isset($registry[$hookName][$priority])) {
                return false;
            }

            if ($callback === null) {
                unset($registry[$hookName][$priority]);
            } else {
                $registry[$hookName][$priority] = array_filter(
                    $registry[$hookName][$priority],
                    fn($hook) => !$this->callbacksAreEqual($hook['callback'], $callback)
                );

                if (empty($registry[$hookName][$priority])) {
                    unset($registry[$hookName][$priority]);
                }
            }
        } else {
            foreach ($registry[$hookName] as $prio => &$hooks) {
                $hooks = array_filter(
                    $hooks,
                    fn($hook) => !$this->callbacksAreEqual($hook['callback'], $callback)
                );

                if (empty($hooks)) {
                    unset($registry[$hookName][$prio]);
                }
            }
            unset($hooks);
        }

        if (empty($registry[$hookName])) {
            unset($registry[$hookName]);
        }

        return true;
    }

    protected function callbacksAreEqual(callable|string $a, callable|string $b): bool
    {
        return $this->normalizeCallback($a) === $this->normalizeCallback($b);
    }

    protected function normalizeCallback(callable|string $callback): string
    {
        if (is_array($callback)) {
            [$objectOrClass, $method] = $callback;
            $class = is_object($objectOrClass) ? $objectOrClass::class : (string) $objectOrClass;
            return $class . '::' . $method;
        }

        return (string) $callback;
    }
}
