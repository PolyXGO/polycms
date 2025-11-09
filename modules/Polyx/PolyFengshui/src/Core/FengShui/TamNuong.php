<?php

namespace Modules\Polyx\PolyFengshui\Core\FengShui;

use Modules\Polyx\PolyFengshui\Core\FengShui\Date;
use Modules\Polyx\PolyFengshui\Core\Helpers\DateTime;

class TamNuong
{
    private static array $NGAY_TAM_NUONG = [3, 7, 13, 18, 22, 27];

    /**
     * Lấy danh sách ngày Tam Nương trong tháng
     * @return array Danh sách ngày
     */
    public static function getAll(): array
    {
        return self::$NGAY_TAM_NUONG;
    }
}
