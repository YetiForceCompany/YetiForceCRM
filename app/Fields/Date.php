<?php
/**
 * Tools for datetime class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields;

/**
 * DateTime class.
 */
class Date
{
	public static $jsDateFormat = [
		'dd-mm-yyyy' => 'd-m-Y',
		'mm-dd-yyyy' => 'm-d-Y',
		'yyyy-mm-dd' => 'Y-m-d',
		'dd.mm.yyyy' => 'd.m.Y',
		'mm.dd.yyyy' => 'm.d.Y',
		'yyyy.mm.dd' => 'Y.m.d',
		'dd/mm/yyyy' => 'd/m/Y',
		'mm/dd/yyyy' => 'm/d/Y',
		'yyyy/mm/dd' => 'Y/m/d',
	];

	/**
	 * Numeric representation of the day of the week.
	 *
	 * @var array
	 */
	public static $dayOfWeekForJS = [
		'Monday' => 1,
		'Tuesday' => 2,
		'Wednesday' => 3,
		'Thursday' => 4,
		'Friday' => 5,
		'Saturday' => 6,
		'Sunday' => 0,
	];
	/**
	 * ISO-8601 numeric representation of the day of the week.
	 *
	 * @example date('N')
	 *
	 * @var array
	 */
	public static $dayOfWeek = [
		'Monday' => 1,
		'Tuesday' => 2,
		'Wednesday' => 3,
		'Thursday' => 4,
		'Friday' => 5,
		'Saturday' => 6,
		'Sunday' => 7,
	];

	/**
	 * Short days translations.
	 *
	 * @var array
	 */
	public static $shortDaysTranslations = [
		'Sunday' => 'LBL_SM_SUN',
		'Monday' => 'LBL_SM_MON',
		'Tuesday' => 'LBL_SM_TUE',
		'Wednesday' => 'LBL_SM_WED',
		'Thursday' => 'LBL_SM_THU',
		'Friday' => 'LBL_SM_FRI',
		'Saturday' => 'LBL_SM_SAT',
	];

	/**
	 * Current user JS date format.
	 *
	 * @param bool $format
	 *
	 * @return bool|string
	 */
	public static function currentUserJSDateFormat($format = false)
	{
		if ($format) {
			return static::$jsDateFormat[$format];
		}
		return static::$jsDateFormat[\App\User::getCurrentUserModel()->getDetail('date_format')] ?? false;
	}

	/**
	 * This function returns the date in user specified format.
	 * limitation is that mm-dd-yyyy and dd-mm-yyyy will be considered same by this API.
	 * As in the date value is on mm-dd-yyyy and user date format is dd-mm-yyyy then the mm-dd-yyyy
	 * value will be return as the API will be considered as considered as in same format.
	 * this due to the fact that this API tries to consider the where given date is in user date
	 * format. we need a better gauge for this case.
	 *
	 * @param string $value the date which should a changed to user date format
	 *
	 * @return string
	 */
	public static function formatToDisplay($value)
	{
		if (empty($value) || '0000-00-00' === $value || '0000-00-00 00:00:00' === $value) {
			return '';
		}
		if ('now' === $value) {
			$value = null;
		}
		return (new \DateTimeField($value))->getDisplayDate();
	}

	/**
	 * Convert date from database format to user format.
	 *
	 * @param array $range ['2018-02-03','2018-02-04']
	 *
	 * @return array|bool ['03.02.2018','04.02.2018'] or false
	 */
	public static function formatRangeToDisplay($range)
	{
		if (\is_array($range)) {
			if (!empty($range[0]) && !empty($range[1])) {
				return [
					static::formatToDisplay($range[0]),
					static::formatToDisplay($range[1]),
				];
			}
			return false;
		}
		return false;
	}

	/**
	 * Function to get date value for db format.
	 *
	 * @param string $value        Date
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
				if (false !== $x) {
					$value = str_replace($delimiter, '-', $value);
					break;
				}
			}
			[$y, $m, $d] = explode('-', $value);
			if (1 == \strlen($y)) {
				$y = '0' . $y;
			}
			if (1 == \strlen($m)) {
				$m = '0' . $m;
			}
			if (1 == \strlen($d)) {
				$d = '0' . $d;
			}
			$value = implode('-', [$y, $m, $d]);
		}
		return (new \DateTimeField($value))->getDBInsertDateValue();
	}

	/**
	 * Convert date to single items.
	 *
	 * @param string      $date
	 * @param bool|string $format Date format
	 *
	 * @return array Array date list($y, $m, $d)
	 */
	public static function explode($date, $format = false)
	{
		if (empty($format)) {
			$format = 'yyyy-mm-dd';
		}
		switch ($format) {
			case 'dd-mm-yyyy':
				[$d, $m, $y] = array_pad(explode('-', $date, 3), 3, null);
				break;
			case 'mm-dd-yyyy':
				[$m, $d, $y] = array_pad(explode('-', $date, 3), 3, null);
				break;
			case 'yyyy-mm-dd':
				[$y, $m, $d] = array_pad(explode('-', $date, 3), 3, null);
				break;
			case 'dd.mm.yyyy':
				[$d, $m, $y] = array_pad(explode('.', $date, 3), 3, null);
				break;
			case 'mm.dd.yyyy':
				[$m, $d, $y] = array_pad(explode('.', $date, 3), 3, null);
				break;
			case 'yyyy.mm.dd':
				[$y, $m, $d] = array_pad(explode('.', $date, 3), 3, null);
				break;
			case 'dd/mm/yyyy':
				[$d, $m, $y] = array_pad(explode('/', $date, 3), 3, null);
				break;
			case 'mm/dd/yyyy':
				[$m, $d, $y] = array_pad(explode('/', $date, 3), 3, null);
				break;
			case 'yyyy/mm/dd':
				[$y, $m, $d] = array_pad(explode('/', $date, 3), 3, null);
				break;
			default:
				break;
		}
		return [$y, $m, $d];
	}

	/**
	 * Get day from date or datetime.
	 *
	 * @param string $date
	 * @param bool   $shortName
	 * @param mixed  $translated
	 *
	 * @return string
	 */
	public static function getDayFromDate($date, $shortName = false, $translated = false)
	{
		if ($translated) {
			return \App\Language::translate(date($shortName ? 'D' : 'l', strtotime($date)), $shortName ? 'Vtiger' : 'Calendar');
		}
		return date($shortName ? 'D' : 'l', strtotime($date));
	}

	/**
	 * Get short days of week.
	 *
	 * @param bool $byId associative array by day id
	 *
	 * @return array
	 */
	public static function getShortDaysOfWeek(bool $byId = true)
	{
		$days = [];
		foreach (static::$dayOfWeek as $day => $id) {
			if ($byId) {
				$days[$id] = static::$shortDaysTranslations[$day];
			} else {
				$days[static::$shortDaysTranslations[$day]] = $id;
			}
		}
		return $days;
	}

	/**
	 * Gets list of holidays.
	 *
	 * @param string $start
	 * @param string $end
	 *
	 * @return array
	 */
	public static function getHolidays(string $start = '', string $end = ''): array
	{
		if (\App\Cache::has('Date::getHolidays', $start . $end)) {
			return \App\Cache::get('Date::getHolidays', $start . $end);
		}
		$query = (new \App\Db\Query())->from('vtiger_publicholiday');
		if ($start && $end) {
			$query->where(['between', 'holidaydate', $start, $end]);
		}
		$query->orderBy(['holidaydate' => SORT_ASC]);
		$holidays = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$holidays[$row['holidaydate']] = [
				'id' => $row['publicholidayid'],
				'date' => $row['holidaydate'],
				'name' => $row['holidayname'],
				'type' => $row['holidaytype'],
				'day' => \App\Language::translate(date('l', strtotime($row['holidaydate'])), 'PublicHoliday'),
			];
		}
		$dataReader->close();
		\App\Cache::save('Date::getHolidays', $start . $end, $holidays);
		return $holidays;
	}

	/**
	 * Get closest working day from given data.
	 *
	 * @param \DateTime $date
	 * @param string    $modify
	 * @param int       $id
	 *
	 * @return string
	 */
	public static function getWorkingDayFromDate(\DateTime $date, string $modify, int $id = \App\Utils\BusinessHours::DEFAULT_BUSINESS_HOURS_ID): string
	{
		$value = $date->modify($modify)->format('Y-m-d');
		$businessHours = \App\Utils\BusinessHours::getBusinessHoursById($id);
		$workingDays = explode(',', $businessHours['working_days'] ?? '1,2,3,4,5');
		$holidays = [];
		if ($businessHours['holidays'] ?? 1) {
			$holidays = self::getHolidays();
		}
		$iterator = 31;
		while (isset($holidays[$value]) || !\in_array($date->format('N'), $workingDays)) {
			$value = $date->modify($modify[0] . '1 day')->format('Y-m-d');
			if (0 === --$iterator) {
				throw new \App\Exceptions\AppException('Exceeded the recursive limit, a loop might have been created.');
			}
		}
		return $value;
	}

	/**
	 * Method to return date counted only using working days.
	 *
	 * @param \DateTime $date
	 * @param int       $counter
	 * @param bool      $increase
	 * @param int       $id
	 *
	 * @return string
	 */
	public static function getOnlyWorkingDayFromDate(\DateTime $date, int $counter, bool $increase = true, int $id = \App\Utils\BusinessHours::DEFAULT_BUSINESS_HOURS_ID): string
	{
		$value = $date->format('Y-m-d');
		while ($counter-- > 0) {
			$value = self::getWorkingDayFromDate($date, ($increase ? '+' : '-') . '1 day', $id);
		}
		return $value;
	}

	/**
	 * Function changes the date format to the database format without changing the time zone.
	 *
	 * @param string $date
	 * @param string $fromFormat
	 *
	 * @return string
	 */
	public static function sanitizeDbFormat(string $date, string $fromFormat)
	{
		$dbDate = '';
		if ($date) {
			[$y, $m, $d] = self::explode($date, $fromFormat);
			if (!$y || !$m || !$d) {
				if (false !== strpos($date, '-')) {
					$separator = '-';
				} elseif (false !== strpos($date, '.')) {
					$separator = '.';
				} elseif (false !== strpos($date, '/')) {
					$separator = '/';
				}
				$formatToConvert = str_replace(['/', '.'], '-', $fromFormat);
				$dateToConvert = str_replace($separator, '-', $date);
				switch ($formatToConvert) {
				case 'dd-mm-yyyy':
					[$d, $m, $y] = explode('-', $dateToConvert, 3);
					break;
				case 'mm-dd-yyyy':
					[$m, $d, $y] = explode('-', $dateToConvert, 3);
					break;
				case 'yyyy-mm-dd':
					[$y, $m, $d] = explode('-', $dateToConvert, 3);
					break;
				default:
					break;
			}
				$dbDate = $y . '-' . $m . '-' . $d;
			} else {
				$dbDate = $y . '-' . $m . '-' . $d;
			}
		}

		return $dbDate;
	}

	/**
	 * Check if the date is correct.
	 *
	 * @param string      $date
	 * @param string|null $format
	 *
	 * @return bool
	 */
	public static function isValid(string $date, ?string $format = null): bool
	{
		return (false !== strpos($date, '-') || false !== strpos($date, '.') || false !== strpos($date, '/'))
		&& ([$y, $m, $d] = self::explode($date, $format))
		&& is_numeric($m) && is_numeric($d) && is_numeric($y)
		&& checkdate($m, $d, $y) && strtotime("{$y}-{$m}-{$d}");
	}
}
