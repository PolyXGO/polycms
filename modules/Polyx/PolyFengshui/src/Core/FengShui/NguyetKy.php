<?php

namespace Modules\Polyx\PolyFengshui\Core\FengShui;

class NguyetKy
{
    private static array $NGAY_NGUYET_KY = [5, 14, 23];

    /**
     * Lấy danh sách ngày Nguyệt Kỵ trong tháng
     * @return array Danh sách ngày
     */
    public static function getAll(): array
    {
        return self::$NGAY_NGUYET_KY;
    }
}
