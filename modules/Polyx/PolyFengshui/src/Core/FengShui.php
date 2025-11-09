<?php

namespace Modules\Polyx\PolyFengshui\Core;

use Modules\Polyx\PolyFengshui\Core\FengShui\CanChi;
use Modules\Polyx\PolyFengshui\Core\FengShui\DuongCongKy;
use Modules\Polyx\PolyFengshui\Core\FengShui\NguyetKy;
use Modules\Polyx\PolyFengshui\Core\FengShui\TamNuong;

class FengShui
{
    public static function getCAN($sperator = ',')
    {
        $content = CanChi::getCAN($sperator);
        return  $content;
    }

    public static function getCHI($sperator = ',')
    {
        $content = CanChi::getChi($sperator);
        return  $content;
    }

    // NgayTamNuong
    public static function getNgayTamNuong()
    {
        return TamNuong::getAll();
    }

    // NgayNguyetKy
    public static function getNgayNguyetKy()
    {
        return NguyetKy::getAll();
    }

    // DuongCongKy
    public static function getNgayDuongCongKy()
    {
        return DuongCongKy::getAll();
    }

    public static function getNgayDuongCongKyByMonth($thang)
    {
        return DuongCongKy::get($thang);
    }
}
