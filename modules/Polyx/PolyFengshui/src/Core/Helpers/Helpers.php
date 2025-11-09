<?php

namespace Modules\Polyx\PolyFengshui\Core\Helpers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

class Helpers
{
    /**
     * Loại bỏ khoảng trắng thừa trong chuỗi
     * @param string $content Chuỗi cần xử lý
     * @return string Chuỗi đã được làm sạch
     */
    public static function removeWhiteSpace(string $content): string
    {
        return preg_replace('/\s+/', ' ', trim($content));
    }

    public static function ensureAuthorizedDomain(?string $allowedDomain, ?string $currentDomain): void
    {
        if (empty($allowedDomain)) {
            // Không cấu hình domain đồng nghĩa chấp nhận mọi domain
            return;
        }

        if (empty($currentDomain)) {
            throw new AuthorizationException(
                __('Không xác định được domain hiện tại để kiểm tra quyền truy cập.')
            );
        }

        $normalizedAllowed = self::normalizeDomain($allowedDomain);
        $normalizedCurrent = self::normalizeDomain($currentDomain);

        if ($normalizedAllowed === null) {
            throw new AuthorizationException(
                __('Domain được cấu hình không hợp lệ.')
            );
        }

        if (!hash_equals($normalizedAllowed, $normalizedCurrent)) {
            throw new AuthorizationException(
                __('Domain hiện tại không được cấp quyền truy cập cho token này.')
                . ' '
                . sprintf('(%s ≠ %s)', $normalizedCurrent, $normalizedAllowed)
            );
        }
    }

    protected static function normalizeDomain(string $domain): ?string
    {
        $domain = trim($domain);

        if ($domain === '') {
            return null;
        }

        $host = parse_url($domain, PHP_URL_HOST);

        if ($host === false) {
            return null;
        }

        if ($host === null) {
            $host = preg_replace('/^https?:\/\//i', '', $domain);
            $host = Str::before($host, '/');
        }

        $host = trim($host);

        if ($host === '') {
            return null;
        }

        return Str::lower($host);
    }
}
