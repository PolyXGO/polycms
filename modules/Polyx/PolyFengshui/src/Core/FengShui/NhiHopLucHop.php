<?php

namespace Modules\Polyx\PolyFengshui\Core\FengShui;

class NhiHopLucHop
{
    private static $LUC_HOP = [
        'Tý'   => 'Sửu',
        'Sửu'  => 'Tý',
        'Mão'  => 'Tuất',
        'Tuất' => 'Mão',
        'Thìn' => 'Dậu',
        'Dậu'  => 'Thìn',
        'Tỵ'   => 'Thân',
        'Thân' => 'Tỵ',
        'Ngọ'  => 'Mùi',
        'Mùi'  => 'Ngọ',
        'Hợi'  => 'Dần',
        'Dần'  => 'Hợi'
    ];

    public static function getInformation()
    {
        return [
            'display' => 'Nhị Hợp, Lục Hợp',
            'description' => 'Nhị Hợp là 1 cặp đối xứng trong bảng Lục Hợp được tính theo 12 con giáp.'
        ];
    }

    public static function get($chi)
    {
        return self::$LUC_HOP[$chi] ?? '';
    }

    public static function getLucHop()
    {
        $pairs = [];
        $seen = [];

        foreach (self::$LUC_HOP as $chi1 => $chi2) {
            if (!isset($seen[$chi1]) && !isset($seen[$chi2])) {
                $pairs[] = "$chi1 $chi2";
                $seen[$chi1] = true;
                $seen[$chi2] = true;
            }
        }

        return $pairs;
    }
}
