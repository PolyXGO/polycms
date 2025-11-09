<?php
namespace Modules\Polyx\PolyFengshui\Core\FengShui;

class TamHop
{
    public static function getInformation(){
        return [
            'display' => 'Tam Hợp',
            'description' => 'Tam Hợp được tính theo 4 con giáp đối nhau thành dấu + trong vòng trong 12 Con Giáp (Địa Chi). Bắt đầu từ chi nào thì đối diện nó là con giáp chính xung.'];
    }
    public static function get($chi)
    {
        $index = array_search($chi, CanChi::$CHI);
        if ($index === false) {
            return '';
        }

        $result = [];
        for ($i = 1; $i <= 4; $i++) {
            $index = ($index + 4) % count(CanChi::$CHI);
            $result[] = CanChi::$CHI[$index];
        }

        return [$chi, $result[0], $result[1]];
    }
}
