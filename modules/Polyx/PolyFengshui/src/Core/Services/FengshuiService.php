<?php

namespace Modules\Polyx\PolyFengshui\Core\Services;

use Modules\Polyx\PolyFengshui\Core\Helpers\DateTime;
use RuntimeException;

class FengshuiService
{
    /**
     * @param  array<int, int>  $years
     */
    public function getDateInsights(array $years): array
    {
        $goodBadByYear = DateTime::getGoodBadByYearAge($years);
        $goodBadZodiac = DateTime::getGoodBadZodiac();

        return $this->combineYearAndZodiac($goodBadByYear, $goodBadZodiac);
    }

    public function getMovingDate(?string $date): array
    {
        return DateTime::getGoodBadZodiac($date);
    }

    public function getMovingDateLookup(int $checkedYear, ?int $targetYear, ?int $targetMonth, ?int $targetDay): array
    {
        $targetDay = $targetDay ?? 1;

        $goodBadByYear = DateTime::getGoodBadByYearAge(
            [$checkedYear],
            $targetYear ? (string) $targetYear : '',
            $targetMonth ? (string) $targetMonth : '',
            str_pad((string) $targetDay, 2, '0', STR_PAD_LEFT)
        );

        $current = $goodBadByYear['date'][0] ?? null;
        $now = $goodBadByYear['now'] ?? null;

        if ($current === null || $now === null) {
            throw new RuntimeException('Unable to compute feng shui data for the provided parameters.');
        }

        $targetYearFinal = $now->solar['year'] ?? $targetYear;
        $targetMonthFinal = $now->solar['month'] ?? $targetMonth;
        $targetDayFinal = $now->solar['day'] ?? $targetDay;

        $targetDate = sprintf(
            '%s-%s-%s',
            str_pad((string) $targetYearFinal, 4, '0', STR_PAD_LEFT),
            str_pad((string) $targetMonthFinal, 2, '0', STR_PAD_LEFT),
            str_pad((string) $targetDayFinal, 2, '0', STR_PAD_LEFT)
        );

        $goodBadZodiac = DateTime::getGoodBadZodiac($targetDate);

        return $this->combineYearAndZodiac($goodBadByYear, $goodBadZodiac);
    }

    protected function combineYearAndZodiac(array $goodBadByYear, array $goodBadZodiac): array
    {
        $tuHanhXung = $goodBadByYear['date'][0]['tu_hanh_xung']['year']->xung ?? [];
        $thienCanKhac = $goodBadByYear['date'][0]['thien_can']['khac'] ?? null;
        $goodZodiacDays = $goodBadZodiac['fengshui']['good_zodiac_moving_date'] ?? [];

        $goodDays = [];
        $badTuHanhXung = [];
        $badThienCanKhac = [];

        foreach ($goodZodiacDays as $day) {
            $lunar = $day['lunar'] ?? [];
            $chi = $lunar['chi'] ?? null;
            $can = $lunar['can'] ?? null;

            if (!in_array($chi, $tuHanhXung, true) && $can !== $thienCanKhac) {
                $goodDays[] = $day;
            } else {
                if (in_array($chi, $tuHanhXung, true)) {
                    $badTuHanhXung[] = $day;
                }
                if ($can === $thienCanKhac) {
                    $badThienCanKhac[] = $day;
                }
            }
        }

        return [
            'good_bad' => $goodBadByYear,
            'good_zodiac_by_year' => $goodDays,
            'bad_zodiac_by_year' => [
                'note' => 'Ngày phạm Tứ Hành Xung & Thiên Can Khắc',
                'tu_hanh_xung' => $badTuHanhXung,
                'thien_can_khac' => $badThienCanKhac,
            ],
        ];
    }
}

