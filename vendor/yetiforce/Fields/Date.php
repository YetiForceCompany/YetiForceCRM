<?php
/**
 * Tools for datetime class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
namespace App\Fields;

/**
 * DateTime class
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
	 * Current user JS date format.
	 * @param boolean $format
	 * @return boolean|string
	 */
	public static function currentUserJSDateFormat($format = false)
	{
		if ($format) {
			return static::$jsDateFormat[$format];
		} else {
			return static::$jsDateFormat[\App\User::getCurrentUserModel()->getDetail('date_format')];
		}
	}

	/**
	 * This function returns the date in user specified format.
	 * limitation is that mm-dd-yyyy and dd-mm-yyyy will be considered same by this API.
	 * As in the date value is on mm-dd-yyyy and user date format is dd-mm-yyyy then the mm-dd-yyyy
	 * value will be return as the API will be considered as considered as in same format.
	 * this due to the fact that this API tries to consider the where given date is in user date
	 * format. we need a better gauge for this case.
	 * @global Users $current_user
	 * @param Date $cur_date_val the date which should a changed to user date format.
	 * @return Date
	 */
	public static function formatToDisplay($value)
	{
		if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
			return '';
		}
		return (new \DateTimeField($value))->getDisplayDate();
	}

	/**
	 * Function to get date value for db format
	 * @param string $value Date
	 * @param bool $leadingZeros
	 * @return string
	 */
	public static function formatToDb($value, $leadingZeros = false)
	{
		if ($leadingZeros) {
			$delim = ['/', '.'];
			foreach ($delim as $delimiter) {
				$x = strpos($value, $delimiter);
				if ($x === false)
					continue;
				else {
					$value = str_replace($delimiter, '-', $value);
					break;
				}
			}
			list($y, $m, $d) = explode('-', $value);
			if (strlen($y) == 1)
				$y = '0' . $y;
			if (strlen($m) == 1)
				$m = '0' . $m;
			if (strlen($d) == 1)
				$d = '0' . $d;
			$value = implode('-', [$y, $m, $d]);
		}
		return (new \DateTimeField($value))->getDBInsertDateValue();
	}

	/**
	 * Convert date to single items
	 * @param string $date
	 * @param string|bool $format Date format
	 * @return array Array date list($y, $m, $d)
	 */
	public static function explode($date, $format = false)
	{
		if (empty($format)) {
			$format = 'yyyy-mm-dd';
		}
		switch ($format) {
			case 'dd-mm-yyyy': list($d, $m, $y) = explode('-', $date, 3);
				break;
			case 'mm-dd-yyyy': list($m, $d, $y) = explode('-', $date, 3);
				break;
			case 'yyyy-mm-dd': list($y, $m, $d) = explode('-', $date, 3);
				break;
			case 'dd.mm.yyyy': list($d, $m, $y) = explode('.', $date, 3);
				break;
			case 'mm.dd.yyyy': list($m, $d, $y) = explode('.', $date, 3);
				break;
			case 'yyyy.mm.dd': list($y, $m, $d) = explode('.', $date, 3);
				break;
			case 'dd/mm/yyyy': list($d, $m, $y) = explode('/', $date, 3);
				break;
			case 'mm/dd/yyyy': list($m, $d, $y) = explode('/', $date, 3);
				break;
			case 'yyyy/mm/dd': list($y, $m, $d) = explode('/', $date, 3);
				break;
		}
		return [$y, $m, $d];
	}

	/**
	 * Function returning difference in format between date times
	 * @param string $start ex. '2017-07-10 11:45:56
	 * @param string $end ex. 2017-07-30 12:08:19
	 * @param string $format Default %a
	 * @link https://secure.php.net/manual/en/class.dateinterval.php
	 * @link https://secure.php.net/manual/en/dateinterval.format.php
	 * @return string|int difference in format
	 */
	public static function getDiff($start, $end, $format = '%a')
	{
		$interval = (new \DateTime($start))->diff(new \DateTime($end));
		switch ($format) {
			case 'years':
				return $interval->format('%Y');
			case 'months':
				$years = $interval->format('%Y');
				$months = 0;
				if ($years) {
					$months += $years * 12;
				}
				$months += $interval->format('%m');
				return $months;
			case 'days':
				return $interval->format('%a');
			case 'hours':
				$days = $interval->format('%a');
				$hours = 0;
				if ($days) {
					$hours += 24 * $days;
				}
				$hours += $interval->format('%H');
				return $hours;
			case 'minutes':
				$days = $interval->format('%a');
				$minutes = 0;
				if ($days) {
					$minutes += 24 * 60 * $days;
				}
				$hours = $interval->format('%H');
				if ($hours) {
					$minutes += 60 * $hours;
				}
				$minutes += $interval->format('%i');
				return $minutes;
			case 'seconds':
				$days = $interval->format('%a');
				$seconds = 0;
				if ($days) {
					$seconds += 24 * 60 * 60 * $days;
				}
				$hours = $interval->format('%H');
				if ($hours) {
					$seconds += 60 * 60 * $hours;
				}
				$minutes = $interval->format('%i');
				if ($minutes) {
					$seconds += 60 * $minutes;
				}
				$seconds += $interval->format('%s');
				return $seconds;
		}
		return $interval->format($format);
	}
}
