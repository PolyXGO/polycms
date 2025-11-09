<?php

namespace Modules\Polyx\PolyFengshui\Core\Helpers;

use Modules\Polyx\PolyFengshui\Core\FengShui\Date;
use Modules\Polyx\PolyFengshui\Core\FengShui\DuongCongKy;
use Modules\Polyx\PolyFengshui\Core\FengShui\NguyetKy;
use Modules\Polyx\PolyFengshui\Core\FengShui\NhiHopLucHop;
use Modules\Polyx\PolyFengshui\Core\FengShui\TamHop;
use Modules\Polyx\PolyFengshui\Core\FengShui\TamNuong;
use Modules\Polyx\PolyFengshui\Core\FengShui\ThienCanHopKhac;
use Modules\Polyx\PolyFengshui\Core\FengShui\TuHanhXung;

class DateTime
{
    /**
     * Get the current date and time based on the specified timezone.
     *
     * @param string $timezone The timezone to use (default: "Asia/Ho_Chi_Minh").
     * @return object An object containing full date-time details.
     */
    public static function getCurrentDateTime($timezone = "Asia/Ho_Chi_Minh") {
        date_default_timezone_set($timezone); // Set the specified timezone (default: Vietnam time)

        $now = new \DateTime(); // Get the current date and time

        return (object) [
            'datetime' => $now->format("Y-m-d H:i:s"), // Full date-time
            'date'     => $now->format("Y-m-d"),       // Date only (YYYY-MM-DD)
            'time'     => $now->format("H:i:s"),       // Time only (HH:MM:SS)
            'day'      => $now->format("d"),           // Day (DD)
            'month'    => $now->format("m"),           // Month (MM)
            'year'     => $now->format("Y"),           // Year (YYYY)
            'hour'     => $now->format("H"),           // Hour (24-hour format)
            'minute'   => $now->format("i"),           // Minute (MM)
            'second'   => $now->format("s")            // Second (SS)
        ];
    }

    public static function getCustomDateTime($year,  $month, $day='01', $timezone = "Asia/Ho_Chi_Minh") {
        // Set the specified timezone (default: Vietnam time)
        date_default_timezone_set($timezone);

        // Create a new DateTime object
        $customDateTime = new \DateTime();

        // Set the date to the provided day, month, and year
        $customDateTime->setDate((int)$year, (int)$month, (int)$day);

        // Set the time to midnight (00:00:00) by default
        $customDateTime->setTime(0, 0, 0);

        return (object) [
            'datetime' => $customDateTime->format("Y-m-d H:i:s"), // Full date-time (YYYY-MM-DD HH:MM:SS)
            'date'     => $customDateTime->format("Y-m-d"),       // Date only (YYYY-MM-DD)
            'time'     => $customDateTime->format("H:i:s"),       // Time only (HH:MM:SS)
            'day'      => $customDateTime->format("d"),           // Day (DD)
            'month'    => $customDateTime->format("m"),           // Month (MM)
            'year'     => $customDateTime->format("Y"),           // Year (YYYY)
            'hour'     => $customDateTime->format("H"),           // Hour (24-hour format)
            'minute'   => $customDateTime->format("i"),           // Minute (MM)
            'second'   => $customDateTime->format("s")            // Second (SS)
        ];
    }

    public static function getSolarLunarDateTime($solar_year ='', $solar_month ='', $solar_day =''){
        $dateInstance = Date::getInstance();

        $solarDateNow = (!empty($solar_year) && !empty($solar_month))
    ? self::getCustomDateTime($solar_year, $solar_month, $solar_day ?? 1)
    : self::getCurrentDateTime();

        $lunarDate = $dateInstance->getLunarDate($solarDateNow->day, $solarDateNow->month, $solarDateNow->year);
        $lunarMonth = $lunarDate->month;
        $lunarYear = $lunarDate->year;

        $monthCan = $dateInstance->getMonthCan($lunarMonth, $lunarYear);
        $monthChi = $dateInstance->getMonthChi($lunarMonth);

        $dayCan = $dateInstance->getDayCan($lunarDate->jd);
        $dayChi = $dateInstance->getDayChi($lunarDate->jd);

        $yearCan = $dateInstance->getYearCan($lunarYear);
        $yearChi = $dateInstance->getYearChi($lunarYear);

        $tietKhiCurrent = $dateInstance->getTietKhi($lunarDate->jd);

        return (object)[
            'solar_terms' => $tietKhiCurrent,
            'solar' => [
                'day' => $solarDateNow->day,
                'month' => $solarDateNow->month,
                'year' => $solarDateNow->year
            ],
            'lunar' => [
                'day' => $lunarDate->day,
                'month' => $lunarDate->month,
                'year' => $lunarDate->year,

                'day_can' => $dayCan,
                'day_chi' => $dayChi,

                'month_can' => $monthCan,
                'month_chi' => $monthChi,

                'year_can' => $yearCan,
                'year_chi' => $yearChi
            ],
        ];
    }

    public static function  getGoodBadZodiac($yearCheck = '')
    {
        $now = null;
        if ($yearCheck) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $yearCheck)) {
                return [
                    'error' => 'Invalid date format. Expected YYYY-MM-DD.'
                ];
            }

            list($year, $month, $day) = explode('-', $yearCheck);
            if (!checkdate((int)$month, (int)$day, (int)$year)) {
                return [
                    'error' => 'Invalid date value. The date does not exist.'
                ];
            }

            $now = new \DateTime($yearCheck, new \DateTimeZone("Asia/Ho_Chi_Minh"));
        }

        if ($now) {
            $solarDateNow = (object) [
                'datetime' => $now->format("Y-m-d H:i:s"), // Full date-time
                'date'     => $now->format("Y-m-d"),       // Date only (YYYY-MM-DD)
                'time'     => $now->format("H:i:s"),       // Time only (HH:MM:SS)
                'day'      => $now->format("d"),           // Day (DD)
                'month'    => $now->format("m"),           // Month (MM)
                'year'     => $now->format("Y"),           // Year (YYYY)
                'hour'     => $now->format("H"),           // Hour (24-hour format)
                'minute'   => $now->format("i"),           // Minute (MM)
                'second'   => $now->format("s")            // Second (SS)
            ];
        } else {
            $solarDateNow = DateTime::getCurrentDateTime();
        }

        $dateInstance = Date::getInstance();

        $lunarDate = $dateInstance->getLunarDate($solarDateNow->day, $solarDateNow->month, $solarDateNow->year);
        $lunarMonth = $lunarDate->month;
        $lunarYear = $lunarDate->year;

        $monthCan = $dateInstance->getMonthCan($lunarMonth, $lunarYear);
        $monthChi = $dateInstance->getMonthChi($lunarMonth);

        $dayCan = $dateInstance->getDayCan($lunarDate->jd);
        $dayChi = $dateInstance->getDayChi($lunarDate->jd);

        $yearCan = $dateInstance->getYearCan($lunarYear);
        $yearChi = $dateInstance->getYearChi($lunarYear);

        $NgayHoangDaoMonth = [];

        // Exlude: TamNuong, NguyetKy, DuongCongKy
        $NgayNguyetKy = NguyetKy::getAll();
        $NgayTamNuong = TamNuong::getAll();
        $lunarDayExclude = array_unique(array_merge($NgayNguyetKy, $NgayTamNuong));
        $NgayDuongCongKyTemp = [];
        // Exlude: TamNuong, NguyetKy, DuongCongKy

        $totalDaysInMonth = Date::pxg_get_day_of_month($solarDateNow->month, $solarDateNow->year);

        for ($day = 1; $day <= $totalDaysInMonth; $day++) {
            $lunarDayInfo = $dateInstance->getLunarDate($day, $solarDateNow->month, $solarDateNow->year);
            $dayChiCurrent = $dateInstance->getDayChi($lunarDayInfo->jd);
            $monthChiCurrent = $dateInstance->getMonthChi($lunarDayInfo->month);

            $dayHoangDaoHacDao = $dateInstance->pxg_get_KIETHUNGNHAT($monthChiCurrent, $dayChiCurrent);

            if ($dayHoangDaoHacDao->is_good_zodiac) {

                // Exlude: TamNuong, NguyetKy, DuongCongKy
                $NgayDuongCongKy = DuongCongKy::get($lunarDayInfo->month);
                $NgayDuongCongKyTemp = array_unique(array_merge($NgayDuongCongKy));
                $lunarDayExclude = array_unique(array_merge($lunarDayExclude, $NgayDuongCongKy));

                $tietKhiCurrent = $dateInstance->getTietKhi($lunarDayInfo->jd);

                // Exclude: TamNuong, NguyetKy, DuongCongKy

                if (!in_array($lunarDayInfo->day, $lunarDayExclude)) {

                    // Good Hour Zodiac
                    $goodHourZodiac = $dateInstance->getGioHoangDaoKietHung($lunarDayInfo->jd);
                    // Good Hour Zodiac
                    $dayHoangDaoHacDao->good_zodiac_hour = $goodHourZodiac;

                    $dayCanCurrent = $dateInstance->getDayCan($lunarDayInfo->jd);
                    $dayChiCurrent = $dateInstance->getDayChi($lunarDayInfo->jd);

                    $NgayHoangDaoMonth[] = [
                        'solar_terms' => $tietKhiCurrent,
                        'day_status' => ($day < $solarDateNow->day) ? 'past' : (($day > $solarDateNow->day) ? 'future' : 'today'),
                        'solar' => [
                            'day' => $day,
                            'month' => $solarDateNow->month,
                            'year' => $solarDateNow->year
                        ],
                        'lunar' => [
                            'day' => $lunarDayInfo->day,
                            'month' => $lunarDayInfo->month,
                            'year' => $lunarDayInfo->year,
                            'can' => $dayCanCurrent,
                            'chi' => $dayChiCurrent,
                            'can_chi' =>   $dayCanCurrent . ' ' . $dayChiCurrent,
                        ],
                        'zodiac' => $dayHoangDaoHacDao
                    ];
                }
            }
        }

        $tietKhi = $dateInstance->getTietKhi($lunarDate->jd);

        // Good Hour Zodiac Today
        $goodHourZodiacToday = $dateInstance->getGioHoangDaoKietHung($lunarDate->jd);
        // Good Hour Zodiac Today

        $data = [
            'note' => 'Good/Bad Zodiac Days exclude Tam Nương Days, Nguyệt Kỵ Days, and Dương Công Kỵ Days',
            'days_of_month' => $totalDaysInMonth,
            'date' => [
                'solar_terms' => $tietKhi,
                'solar' => $solarDateNow,
                'lunar' => $lunarDate,
                'day' => [
                    'can_chi' => $dayCan . ' ' . $dayChi,
                    'can' => $dayCan,
                    'chi' => $dayChi
                ],
                'month' => [
                    'can_chi' => $monthCan . ' ' . $monthChi,
                    'can' => $monthCan,
                    'chi' => $monthChi
                ],
                'year' => [
                    'can_chi' => $yearCan . ' ' . $yearChi,
                    'can' => $yearCan,
                    'chi' => $yearChi
                ],
                'good_zodiac_hour' => $goodHourZodiacToday,
            ],
            'fengshui' => [
                'good_zodiac_day' => $dateInstance::$NGAYHOANGDAO,
                'bad_zodiac_day' => $dateInstance::$NGAYHACDAO,
                'kiet_hung_nhat' => $dateInstance->pxg_get_KIETHUNGNHAT($monthChi, $dateInstance->getDayChi($lunarDate->jd)),
                'tam_nuong' => [
                    'list' => $NgayTamNuong,
                    'solar_lunar' => $dateInstance->getAllSolarLunarRelated($solarDateNow->month,  $solarDateNow->year, TamNuong::getAll())
                ],
                'nguyet_ky' => [
                    'list' => $NgayNguyetKy,
                    'solar_lunar' => $dateInstance->getAllSolarLunarRelated($solarDateNow->month,  $solarDateNow->year, NguyetKy::getAll())
                ],
                'duong_cong_ky' => [
                    'list' => $NgayDuongCongKyTemp,
                    'solar_lunar' => $dateInstance->getAllSolarLunarRelated($solarDateNow->month,  $solarDateNow->year, $NgayDuongCongKyTemp ?? [])
                ],
                'good_zodiac_moving_date' => $NgayHoangDaoMonth,
            ]
        ];
        return $data;
    }

    // /api/feng-shui/v2/date?years=1987
    public static function getGoodBadByYearAge($years, $target_year ='',  $target_month='', $target_day = '01'){
        if (empty($years)) {
            return '';
        }

        $dateInstance = Date::getInstance();
        $dateSolarLunarNow = self::getSolarLunarDateTime($target_year, $target_month, $target_day);

        $lunar_now_month_chi = $dateSolarLunarNow->lunar['month_chi'];
        $lunar_now_day_chi = $dateSolarLunarNow->lunar['day_chi'];

        $data = array_map(function ($year) use ($dateInstance, $lunar_now_month_chi, $lunar_now_day_chi) {
            $yearCanChi = $dateInstance->getYearCanChi($year);
            $arrYearCanChi = explode(' ', $yearCanChi);
            return [
                'month_chi' => $lunar_now_month_chi,
                'lunar' => [
                    'year' => $year,
                    'can' => $arrYearCanChi[0],
                    'chi' => $arrYearCanChi[1],
                ], // Năm cần xem
                'tu_hanh_xung' => [
                    'year' => TuHanhXung::get($arrYearCanChi[1]), // Xung năm
                    'month' => TuHanhXung::get($lunar_now_month_chi), // Xung tháng
                    'day' => TuHanhXung::get($lunar_now_day_chi) // Xung ngày
                ],
                'tam_hop' => TamHop::get($arrYearCanChi[1]),
                'nhi_hop' => NhiHopLucHop::get($arrYearCanChi[1]),
                'luc_hop' => NhiHopLucHop::getLucHop(),
                'thien_can' => [
                    'hop' => ThienCanHopKhac::getHop($arrYearCanChi[0]),
                    'khac' => ThienCanHopKhac::getKhac($arrYearCanChi[0]),
                    'hops' => ThienCanHopKhac::getDanhSachHop(),
                    'khacs' => ThienCanHopKhac::getDanhSachKhac()
                ],
            ];
        }, $years);

        return [
            'now' => $dateSolarLunarNow,
            'date' => $data
        ];
    }
}
