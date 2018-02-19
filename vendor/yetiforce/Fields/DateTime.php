<?php
/**
 * Tools for datetime class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\Fields;

/**
 * DateTime class.
 */
class DateTime
{
    /**
     * Function returns the date in user specified format.
     *
     * @param string $value Date time
     *
     * @return string
     */
    public static function formatToDisplay($value)
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        if ($value === 'now') {
            $value = null;
        }

        return (new \DateTimeField($value))->getDisplayDateTimeValue();
    }

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
     * Convert time (seconds) to H:i:s format elapsed - not clock time.
     *
     * @param int $sec seconds elapsed
     *
     * @return string "120:12:12"
     */
    public static function secondsToHmsElapsed(int $sec)
    {
        $hours = floor($sec / 3600);
        $minutes = floor(($sec / 60) % 60);
        $seconds = $sec % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Function to get date and time value for db format.
     *
     * @param string $value        Date time
     * @param bool   $leadingZeros
     *
     * @return string
     */
    public static function formatToDb($value, $leadingZeros = false)
    {
        if ($leadingZeros) {
            $delim = ['/', '.'];
            foreach ($delim as $delimiter) {
                $x = strpos($value, $delimiter);
                if ($x === false) {
                    continue;
                } else {
                    $value = str_replace($delimiter, '-', $value);
                    break;
                }
            }
            list($y, $m, $d) = explode('-', $value);
            if (strlen($y) == 1) {
                $y = '0'.$y;
            }
            if (strlen($m) == 1) {
                $m = '0'.$m;
            }
            if (strlen($d) == 1) {
                $d = '0'.$d;
            }
            $value = implode('-', [$y, $m, $d]);
            $valueList = explode(' ', $value);
            $dbTimeValue = $valueList[1];
            if (!empty($dbTimeValue) && strpos($dbTimeValue, ':') === false) {
                $dbTimeValue = $dbTimeValue.':';
            }
            if (!empty($dbTimeValue) && strrpos($dbTimeValue, ':') == (strlen($dbTimeValue) - 1)) {
                $dbTimeValue = $dbTimeValue.'00';
            }

            return (new \DateTimeField($valueList[0].' '.$dbTimeValue))->getDBInsertDateTimeValue();
        }

        return (new \DateTimeField($value))->getDBInsertDateTimeValue();
    }

    /**
     * The function returns the date according to the user's settings.
     *
     * @param string $dateTime Date time
     *
     * @return string
     */
    public static function formatToViewDate($dateTime)
    {
        switch (\App\User::getCurrentUserModel()->getDetail('view_date_format')) {
            case 'PLL_FULL':
                return '<span title="'.\Vtiger_Util_Helper::formatDateDiffInStrings($dateTime).'">'.static::formatToDisplay($dateTime).'</span>';
            case 'PLL_ELAPSED':
                return '<span title="'.static::formatToDay($dateTime).'">'.\Vtiger_Util_Helper::formatDateDiffInStrings($dateTime).'</span>';
            case 'PLL_FULL_AND_DAY':
                return '<span title="'.\Vtiger_Util_Helper::formatDateDiffInStrings($dateTime).'">'.static::formatToDay($dateTime).'</span>';
        }

        return '-';
    }

    /**
     * Function to parse dateTime into days.
     *
     * @param string $dateTime Date time
     * @param bool   $allday
     *
     * @return string
     */
    public static function formatToDay($dateTime, $allday = false)
    {
        $dateTimeInUserFormat = explode(' ', static::formatToDisplay($dateTime));
        if (count($dateTimeInUserFormat) === 3) {
            list($dateInUserFormat, $timeInUserFormat, $meridiem) = $dateTimeInUserFormat;
        } else {
            list($dateInUserFormat, $timeInUserFormat) = $dateTimeInUserFormat;
            $meridiem = '';
        }
        $formatedDate = $dateInUserFormat;
        $dateDay = \App\Language::translate(Date::getDayFromDate($dateTime), 'Calendar');
        if (!$allday) {
            $timeInUserFormat = explode(':', $timeInUserFormat);
            if (count($timeInUserFormat) === 3) {
                list($hours, $minutes, $seconds) = $timeInUserFormat;
            } else {
                list($hours, $minutes) = $timeInUserFormat;
                $seconds = '';
            }
            $displayTime = $hours.':'.$minutes.' '.$meridiem;
            $formatedDate .= ' '.\App\Language::translate('LBL_AT').' '.$displayTime;
        }
        $formatedDate .= " ($dateDay)";

        return $formatedDate;
    }

    /**
     * Format elapsed time to short display value.
     *
     * @param int      $hour
     * @param int      $min
     * @param int|bool $sec  if is provided as int then will be displayed
     *
     * @return string
     */
    public static function formatToShortHourText($hour, $min, $sec = false)
    {
        $result = '';
        if ($hour) {
            $result .= $hour.\App\Language::translate('LBL_H');
        }
        if ($hour || $min) {
            $result .= " {$min}".\App\Language::translate('LBL_M');
        }
        if ($sec !== false) {
            $result .= " {$sec}".\App\Language::translate('LBL_S');
        }
        if (!$hour && !$min && $sec === false) {
            $result = '0'.\App\Language::translate('LBL_M');
        }

        return trim($result);
    }

    /**
     * Format elapsed time to full display value.
     *
     * @param int      $hour
     * @param int      $min
     * @param int|bool $sec  if is provided as int then will be displayed
     *
     * @return string
     */
    public static function formatToFullHourText($hour, $min, $sec = false)
    {
        $result = '';
        if ($hour) {
            $result .= "{$hour} ".\App\Language::translate('LBL_HOURS');
        }
        if ($hour || $min) {
            $result .= " {$min} ".\App\Language::translate('LBL_MINUTES');
        }
        if ($sec !== false) {
            $result .= " {$sec} ".\App\Language::translate('LBL_SECONDS');
        }
        if (!$hour && !$min && $sec === false) {
            $result = '0 '.\App\Language::translate('LBL_MINUTES');
        }

        return trim($result);
    }

    /**
     * The function returns the decimal format of the time.
     *
     * @param int    $decTime
     * @param string $type    Values: short, full
     *
     * @return string
     */
    public static function formatToHourText($decTime, string $type)
    {
        $hour = floor($decTime);
        $min = floor(($decTime - $hour) * 60);
        $sec = round((($decTime - $hour) * 60 - $min) * 60);
        switch ($type) {
            case 'short':
                return self::formatToShortHourText($hour, $min);
            case 'full':
                return self::formatToFullHourText($hour, $min);
            case 'short_with_seconds':
                return self::formatToShortHourText($hour, $min, $sec);
            case 'full_with_seconds':
                return self::formatToFullHourText($hour, $min, $sec);
        }
    }

    /**
     * Time zone cache.
     *
     * @var string
     */
    protected static $databaseTimeZone = false;

    /**
     * Get system time zone.
     *
     * @return string
     */
    public static function getTimeZone()
    {
        if (!static::$databaseTimeZone) {
            $defaultTimeZone = date_default_timezone_get();
            if (empty($defaultTimeZone)) {
                $defaultTimeZone = AppConfig::main('default_timezone');
            }
            static::$databaseTimeZone = $defaultTimeZone;
        }

        return static::$databaseTimeZone;
    }
}
