<?php

namespace Modules\Polyx\PolyFengshui\Core\FengShui;

class DuongCongKy
{
    private static array $NGAY_DUONG_CONG_KY = [
        1  => [13],
        2  => [12],
        3  => [9],
        4  => [7],
        5  => [5],
        6  => [3],
        7  => [8, 29],
        8  => [27],
        9  => [25],
        10 => [23],
        11 => [21],
        12 => [19]
    ];

    /**
     * Lấy danh sách ngày Dương Công Kỵ của một tháng bất kỳ
     * @param int $thang Tháng âm lịch (1-12)
     * @return array Danh sách ngày kỵ trong tháng (nếu có)
     */
    public static function get(int $thang): array
    {
        return self::$NGAY_DUONG_CONG_KY[$thang] ?? [];
    }

    public static function getAll(): array
    {
        return array_values(array_merge(...array_values(self::$NGAY_DUONG_CONG_KY)));
    }
    
    
}
