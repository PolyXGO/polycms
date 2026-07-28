<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Support;

use App\Facades\Hook;
use Illuminate\Support\Facades\Log;
use Throwable;

class MTOptimizeHooks
{
    public static function applyFilters(string $hookName, mixed $value, mixed ...$args): mixed
    {
        $hook = self::normalizeHookName($hookName);

        try {
            return Hook::applyFilters($hook, $value, ...$args);
        } catch (Throwable $exception) {
            self::reportHookException($exception, $hook, [
                'mode' => 'filter',
                'args_count' => count($args),
            ]);

            return $value;
        }
    }

    public static function doAction(string $hookName, mixed ...$args): void
    {
        $hook = self::normalizeHookName($hookName);

        try {
            Hook::doAction($hook, ...$args);
        } catch (Throwable $exception) {
            self::reportHookException($exception, $hook, [
                'mode' => 'action',
                'args_count' => count($args),
            ]);
        }
    }

    public static function normalizeHookName(string $hookName): string
    {
        $hook = trim($hookName);

        if ($hook === '') {
            return 'mtoptimize/unknown';
        }

        $hook = str_replace('mtoptimize.', 'mtoptimize/', $hook);

        if (!str_starts_with($hook, 'mtoptimize/')) {
            $hook = 'mtoptimize/' . ltrim(str_replace('.', '/', $hook), '/');
        }

        return $hook;
    }

    protected static function reportHookException(Throwable $exception, string $hookName, array $extra = []): void
    {
        $payload = array_merge([
            'hook' => $hookName,
            'message' => $exception->getMessage(),
            'exception' => $exception::class,
        ], $extra);

        if (config('app.debug')) {
            $payload['trace'] = $exception->getTraceAsString();
        }

        Log::warning('[MTOptimize] Hook execution failed', $payload);
    }
}
