<?php

namespace Modules\Polyx\PolyFengshui\Core\Interfaces;

interface DateTimeInterface
{
    /**
     * Get the current date and time based on the specified timezone.
     *
     * @param string $timezone The timezone to use (default: "Asia/Ho_Chi_Minh").
     * @return object An object containing full date-time details.
     */
    public static function getCurrentDateTime($timezone = "Asia/Ho_Chi_Minh");

    /**
     * Get the Solar-Lunar date details.
     *
     * @return object An object containing solar and lunar date information.
     */
    public static function getSolarLunarDateTime();
}
