<?php

/**
 * Tools for datetime class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafał Pospiech <r.pospiech@yetiforce.com>
 */

namespace App\Fields;

/**
 * Time class.
 */
class Time
{
    /**
     * Convert elapsed time from "H:i:s" to decimal equvalent.
     *
     * @param string $time "12:00:00"
     *
     * @return float
     */
    public static function timeToDecimal(string $time)
    {
        $hms = explode(':', $time);
        $decTime = $hms[0] + ($hms[1] / 60) + ($hms[2] / 3600);

        return $decTime;
    }

    /**
     * Format elapsed time to short display value.
     *
     * @param float    $decTime time in decimal format 1.5 = 1h 30m
     * @param string   $type    hour text format 'short' or 'full'
     * @param int|bool $sec     if is provided as int then will be displayed
     *
     * @return string
     */
    public static function formatToHourText($decTime, $type = 'short', $sec = false)
    {
        $short = $type === 'short';

        $hour = floor($decTime);
        $min = floor(($decTime - $hour) * 60);
        $sec = round((($decTime - $hour) * 60 - $min) * 60);

        $result = '';
        if ($hour) {
            $result .= $hour.$short ? \App\Language::translate('LBL_H') : ' '.\App\Language::translate('LBL_HOURS');
        }
        if ($hour || $min) {
            $result .= " {$min}".$short ? \App\Language::translate('LBL_M') : ' '.\App\Language::translate('LBL_MINUTES');
        }
        if ($sec !== false) {
            $result .= " {$sec}".$short ? \App\Language::translate('LBL_S') : ' '.\App\Language::translate('LBL_SECONDS');
        }
        if (!$hour && !$min && $sec === false) {
            $result = '0'.$short ? \App\Language::translate('LBL_M') : ' '.\App\Language::translate('LBL_MINUTES');
        }

        return trim($result);
    }
}
