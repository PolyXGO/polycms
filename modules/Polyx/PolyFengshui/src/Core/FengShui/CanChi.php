<?php

namespace Modules\Polyx\PolyFengshui\Core\FengShui;

use Modules\Polyx\PolyFengshui\Core\Helpers\Helpers;

class CanChi
{
    public static array $CAN_AM = ["Ất", "Đinh", "Kỷ", "Tân", "Quý"];
    public static array $CAN_DUONG = ["Giáp", "Bính", "Canh", "Nhâm", "Mậu"];
    public static array $CAN = ["Giáp", "Ất", "Bính", "Đinh", "Mậu", "Kỷ", "Canh", "Tân", "Nhâm", "Quý"];
    public static array $CHI = ["Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi"];

    public static function getCAN($separator = ','): string
    {
        return Helpers::removeWhiteSpace(implode($separator . ' ', self::$CAN));
    }

    public static function getCHI($separator = ','): string
    {
        return Helpers::removeWhiteSpace(implode($separator . ' ', self::$CHI));
    }
}
