<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

class VtDateTime
{

	public $second = 0;
	public $minute = 0;
	public $hour = 0;
	public $z_hour = '00';
	public $day;
	public $z_day;
	public $week;
	public $month;
	public $z_month;
	public $year;
	public $dayofweek;
	public $dayofyear;
	public $daysinmonth;
	public $daysinyear;
	public $dayofweek_inshort;
	public $dayofweek_inlong;
	public $month_inshort;
	public $month_inlong;
	public $ts;
	public $offset;
	public $format;
	public $tz;
	public $ts_def;

	/**
	 * Constructor for VtDateTime class
	 * @param array  $timearr - collection of string
	 * @param string $check   - check string
	 */
	public function __construct(&$timearr, $check)
	{
		if (!isset($timearr) || count($timearr) == 0) {
			$this->setDateTime(null);
		} else if (isset($timearr['ts'])) {
			$this->setDateTime($time['ts']);
		} else {
			if (isset($timearr['hour']) && $timearr['hour'] !== '') {
				$this->hour = $timearr['hour'];
			}
			if (isset($timearr['min']) && $timearr['min'] !== '') {
				$this->minute = $timearr['min'];
			}
			if (isset($timearr['sec']) && $timearr['sec'] !== '') {
				$this->second = $timearr['sec'];
			}
			if (isset($timearr['day']) && $timearr['day'] !== '') {
				$this->day = $timearr['day'];
			}
			if (isset($timearr['week']) && $timearr['week'] !== '') {
				$this->week = $timearr['week'];
			}
			if (isset($timearr['month']) && $timearr['month'] !== '') {
				$this->month = $timearr['month'];
			}
			if (isset($timearr['year']) && $timearr['year'] >= 1970) {
				$this->year = $timearr['year'];
			} else {
				return null;
			}
		}
		if ($check) {
			$this->getDateTime();
		}
	}

	/**
	 * function to get date and time using index
	 * @param integer       $index - number between 0 to 23
	 * @param string        $day   - date
	 * @param string        $month - month
	 * @param string        $year  - year
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getTodayDatetimebyIndex($index, $day = '', $month = '', $year = '')
	{
		if ($day === '')
			$day = $this->day;
		if ($month === '')
			$month = $this->month;
		if ($year === '')
			$year = $this->year;
		$day_array = [];

		if ($index < 0 || $index > 23) {
			throw new \App\Exceptions\AppException('hour is invalid');
		}

		$day_array['hour'] = $index;
		$day_array['min'] = 0;
		$day_array['day'] = $day;
		$day_array['month'] = $month;
		$day_array['year'] = $year;
		$datetimevalue = new VtDateTime($day_array, true);
		return $datetimevalue;
	}

	/**
	 * function to get days in week using index
	 * @param integer       $index - number between 1 to 7
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getThisweekDaysbyIndex($index)
	{
		$week_array = [];
		if ($index < 1 || $index > 7) {
			throw new \App\Exceptions\AppException('day is invalid');
		}
		$week_array['day'] = $this->day + ($index - $this->dayofweek);
		$week_array['month'] = $this->month;
		$week_array['year'] = $this->year;
		$datetimevalue = new VtDateTime($week_array, true);
		return $datetimevalue;
	}

	/**
	 * function to get days in month using index
	 *
	 * This function will be deprecated.
	 * The newer version is getThisMonthsDayByIndex() and should be used wherever possible
	 *
	 * @param integer       $index - number between 0 to 42
	 * @param string        $day   - date
	 * @param string        $month - month
	 * @param string        $year  - year
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getThismonthDaysbyIndex($index, $day = '', $month = '', $year = '')
	{
		if ($day == '')
			$day = $index + 1;
		if ($month == '')
			$month = $this->month;
		if ($year == '')
			$year = $this->year;
		$month_array = [];
		$month_array['day'] = $day;
		$month_array['month'] = $month;
		$month_array['year'] = $year;
		$datetimevalue = new VtDateTime($month_array, true);
		return $datetimevalue;
	}

	/**
	 * function to get months in year using index
	 * @param integer       $index - number between 0 to 11
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getThisyearMonthsbyIndex($index)
	{
		$year_array = [];
		$year_array['day'] = 1;
		if ($index < 0 || $index > 11) {
			throw new \App\Exceptions\AppException('month is invalid');
		}
		$year_array['month'] = $index + 1;
		$year_array['year'] = $this->year;
		$datetimevalue = new VtDateTime($year_array, true);
		return $datetimevalue;
	}

	/**
	 * function to get hour end time
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getHourendtime()
	{
		$date_array = [];
		$date_array['hour'] = $this->hour;
		$date_array['min'] = 59;
		$date_array['day'] = $this->day;
		$date_array['sec'] = 59;
		$date_array['month'] = $this->month;
		$date_array['year'] = $this->year;
		$datetimevalue = new VtDateTime($date_array, true);
		return $datetimevalue;
	}

	/**
	 * function to get day end time
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getDayendtime()
	{
		$date_array = [];
		$date_array['hour'] = 23;
		$date_array['min'] = 59;
		$date_array['sec'] = 59;
		$date_array['day'] = $this->day;
		$date_array['month'] = $this->month;
		$date_array['year'] = $this->year;
		$datetimevalue = new VtDateTime($date_array, true);
		return $datetimevalue;
	}

	/**
	 * function to get month end time
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getMonthendtime()
	{
		$date_array = [];
		$date_array['hour'] = 23;
		$date_array['min'] = 59;
		$date_array['sec'] = 59;
		$date_array['day'] = $this->daysinmonth;
		$date_array['month'] = $this->month;
		$date_array['year'] = $this->year;
		$datetimevalue = new VtDateTime($date_array, true);
		return $datetimevalue;
	}

	/**
	 * function to get day of week
	 * return string $this->day  - day (eg: Monday)
	 */
	public function getDate()
	{
		return $this->day;
	}

	/**
	 * function to get month
	 * return string $this->month  - month name
	 */
	public function getMonth()
	{
		return $this->month;
	}

	/**
	 * function to get year
	 */
	public function getYear()
	{
		return $this->year;
	}

	/**
	 * function to get the number of days in a month
	 */
	public function getDaysInMonth()
	{
		return $this->daysinmonth;
	}

	/**
	 * function to set values for VtDateTime object
	 * @param integer   $ts  - Time stamp
	 */
	public function setDateTime($ts)
	{
		$modStrings = vglobal('mod_strings');
		if (empty($ts)) {
			$ts = time();
		}

		$this->ts = $ts;
		$this->ts_def = $this->ts;
		$date_string = date('i::G::H::j::d::t::N::z::L::W::n::m::Y::Z::T::s', $ts);

		list($this->minute, $this->hour, $this->z_hour, $this->day, $this->z_day, $this->daysinmonth, $this->dayofweek, $this->dayofyear, $is_leap, $this->week, $this->month, $this->z_month, $this->year, $this->offset, $this->tz, $this->second) = explode('::', $date_string);

		$this->dayofweek_inshort = $modStrings['cal_weekdays_short'][$this->dayofweek - 1];
		$this->dayofweek_inlong = $modStrings['cal_weekdays_long'][$this->dayofweek - 1];
		$this->month_inshort = $modStrings['cal_month_short'][$this->month];
		$this->month_inlong = $modStrings['cal_month_long'][$this->month];

		$this->daysinyear = 365;

		if ($is_leap == 1) {
			$this->daysinyear += 1;
		}
	}

	/**
	 * function to get values from VtDateTime object
	 */
	public function getDateTime()
	{
		$hour = 0;
		$minute = 0;
		$second = 0;
		$day = 1;
		$month = 1;
		$year = 1970;

		if (isset($this->second) && $this->second !== '') {
			$second = $this->second;
		}
		if (isset($this->minute) && $this->minute !== '') {
			$minute = $this->minute;
		}
		if (isset($this->hour) && $this->hour !== '') {
			$hour = $this->hour;
		}
		if (isset($this->day) && $this->day !== '') {
			$day = $this->day;
		}
		if (isset($this->month) && $this->month !== '') {
			$month = $this->month;
		}

		if (isset($this->year) && $this->year !== '') {
			$year = $this->year;
		} else {
			throw new \App\Exceptions\AppException('year was not set');
		}
		if (empty($hour) && $hour !== 0)
			$hour = 0;
		$this->ts = mktime($hour, $minute, $second, $month, $day, $year);
		$this->setDateTime($this->ts);
	}

	/**
	 * function to get mysql formatted date
	 * return formatted date in string format
	 */
	public function getFormattedDate()
	{
		$date = $this->year . "-" . $this->z_month . "-" . $this->z_day;
		return DateTimeField::convertToUserFormat($date);
	}

	/**
	 * function to get date depends on mode value
	 * @param string $mode  - 'increment' or 'decrement'
	 * return VtDateTime obj
	 */
	public function getChangedDay($mode)
	{
		if ($mode === 'increment')
			$day = $this->day + 1;
		else
			$day = $this->day - 1;
		$date_data = array('day' => $day,
			'month' => $this->month,
			'year' => $this->year
		);
		return new VtDateTime($date_data, true);
	}

	/**
	 * function to get changed week depends on mode value
	 * @param string $mode  - 'increment' or 'decrement'
	 * return VtDateTime obj
	 */
	public function getFirstDayOfChangedWeek($mode)
	{
		$first_day = $this->getThisweekDaysbyIndex(1);
		if ($mode === 'increment')
			$day = $first_day->day + 7;
		else
			$day = $first_day->day - 7;
		$date_data = array('day' => $day,
			'month' => $first_day->month,
			'year' => $first_day->year
		);
		return new VtDateTime($date_data, true);
	}

	/**
	 * function to get month depends on mode value
	 * @param string $mode  - 'increment' or 'decrement'
	 * return VtDateTime obj
	 */
	public function getFirstDayOfChangedMonth($mode)
	{
		$tmpDate['day'] = $this->day;
		$tmpDate['month'] = $this->month;
		$tmpDate['year'] = $this->year;

		if (is_array($arr) && !empty($arr)) {
			$tmpDate['year'] = $arr[0];
			$tmpDate['month'] = $arr[1];
			$tmpDate['day'] = $arr[2];
		}

		if ($mode === 'increment') {
			$month = $tmpDate['month'] + 1;
			$year = $tmpDate['year'];
		} else {
			if ($tmpDate['month'] == 1) {
				$month = 12;
				$year = $tmpDate['year'] - 1;
			} else {
				$month = $tmpDate['month'] - 1;
				$year = $tmpDate['year'];
			}
		}
		$date_data = array(
			'day' => 1,
			'month' => $month,
			'year' => $year
		);

		return new VtDateTime($date_data, true);
	}

	/**
	 * function to get year depends on mode value
	 * @param string $mode  - 'increment' or 'decrement'
	 * return VtDateTime obj
	 */
	public function getFirstDayOfChangedYear($mode)
	{
		if ($mode === 'increment') {
			$year = $this->year + 1;
		} else {
			$year = $this->year - 1;
		}
		$date_data = array('day' => 1,
			'month' => 1,
			'year' => $year
		);
		return new VtDateTime($date_data, true);
	}

	/**
	 * function to get date string
	 * return date string
	 */
	public function getDateStr()
	{
		$array = [];
		if (isset($this->hour) && $this->hour != '') {
			array_push($array, 'hour=' . $this->hour);
		}
		if (isset($this->day) && $this->day != '') {
			array_push($array, 'day=' . $this->day);
		}
		if (isset($this->month) && $this->month) {
			array_push($array, 'month=' . $this->month);
		}
		if (isset($this->year) && $this->year != '') {
			array_push($array, 'year=' . $this->year);
		}
		return ('&' . implode('&', $array));
	}

	/**
	 * function to get days in month using index
	 *
	 * This is the newer version of the function getThismonthDaysbyIndex().
	 * This should be used whereever possible
	 *
	 * @param integer       $index - number between 0 to 42
	 * @param string        $day   - date
	 * @param string        $month - month
	 * @param string        $year  - year
	 * return VtDateTime obj  $datetimevalue
	 */
	public function getThisMonthsDayByIndex($index)
	{
		$day = $index;
		$month = $this->month;
		$year = $this->year;
		$month_array = [];
		$month_array['day'] = $day;
		$month_array['month'] = $month;
		$month_array['year'] = $year;
		$datetimevalue = new VtDateTime($month_array, true);
		return $datetimevalue;
	}
}
