<?php
/**
 * Tools for datetime class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
					static::formatToDisplay($range[1])
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
				[$d, $m, $y] = explode('-', $date, 3);
				break;
			case 'mm-dd-yyyy':
				[$m, $d, $y] = explode('-', $date, 3);
				break;
			case 'yyyy-mm-dd':
				[$y, $m, $d] = explode('-', $date, 3);
				break;
			case 'dd.mm.yyyy':
				[$d, $m, $y] = explode('.', $date, 3);
				break;
			case 'mm.dd.yyyy':
				[$m, $d, $y] = explode('.', $date, 3);
				break;
			case 'yyyy.mm.dd':
				[$y, $m, $d] = explode('.', $date, 3);
				break;
			case 'dd/mm/yyyy':
				[$d, $m, $y] = explode('/', $date, 3);
				break;
			case 'mm/dd/yyyy':
				[$m, $d, $y] = explode('/', $date, 3);
				break;
			case 'yyyy/mm/dd':
				[$y, $m, $d] = explode('/', $date, 3);
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
	 * @return void
	 */
	public static function getHolidays(string $start = '', string $end = '')
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
	 * @param string $date
	 * @param string $direction
	 *
	 * @return string
	 */
	public static function getWorkingDayFromDate(\DateTime &$date, string $modify)
	{
		$value = $date->modify($modify)->format('Y-m-d');
		$holidays = \App\Fields\Date::getHolidays();
		if (isset($holidays[$value])) {
			$value = self::getWorkingDayFromDate($date, $modify[0].'1 day');
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
}
