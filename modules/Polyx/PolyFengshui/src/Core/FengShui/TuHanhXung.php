<?php
namespace Modules\Polyx\PolyFengshui\Core\FengShui;

class TuHanhXung
{
    public static function getInformation(){
        return [
            'display' => 'Tứ Hành Xung',
            'description' => 'Tứ Hành Xung được tính theo 4 con giáp đối nhau thành dấu + trong vòng trong 12 Con Giáp (Địa Chi). Bắt đầu từ chi nào thì đối diện nó là con giáp chính xung.'];
    }
    public static function get($chi)
    {
        $index = array_search($chi, CanChi::$CHI);
        if ($index === false) {
            return '';
        }

        $result = [];
        for ($i = 1; $i <= 3; $i++) {
            $index = ($index + 3) % count(CanChi::$CHI);
            $result[] = CanChi::$CHI[$index];
        }

        return (object)[
            'xung' => [$chi, $result[0], $result[1], $result[2]],
            'chinh_xung' => $result[1]
        ];
    }
}
