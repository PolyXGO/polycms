<?php
namespace Modules\Polyx\PolyFengshui\Core\FengShui;

class ThienCanHopKhac
{
    public static function getInformation()
    {
        return [
            'display' => 'Thiên Can Hợp Khắc',
            'description' => 'Tính toán xung khắc dựa trên 12 thiên can + Ngũ Hành xung khắc'
        ];
    }

    public static function getHop($can)
    {
        $index = array_search($can, CanChi::$CAN);
        if ($index === false) {
            return '';
        }

        $khacIndex = ($index + 5) % count(CanChi::$CAN);

        return CanChi::$CAN[$khacIndex];
    }

    public static function getDanhSachHop()
    {
        $pairs = [];
        $seen = [];
    
        foreach (CanChi::$CAN as $can) {
            $hop = self::getHop($can);
            $pairKey = "$can $hop";
    
            if (!isset($seen[$pairKey]) && !isset($seen["$hop $can"])) {
                $pairs[] = $pairKey;
                $seen[$pairKey] = true;
                $seen["$hop $can"] = true;
            }
        }
        return $pairs;
    }
    

    public static function getKhac($can)
    {
        $index = array_search($can, CanChi::$CAN);
        if ($index === false) {
            return '';
        }

        $khacIndex = ($index + 4) % count(CanChi::$CAN);

        return CanChi::$CAN[$khacIndex];
    }

    public static function getDanhSachKhac()
    {
        $pairs = [];
        foreach (CanChi::$CAN as $can) {
            $khac = self::getKhac($can);
            if (!isset($pairs["$khac $can"]) && !isset($pairs["$can $khac"])) {
                $pairs[] = "$can $khac";
            }
        }
        return $pairs;
    }
}
