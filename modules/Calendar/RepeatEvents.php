<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

/**
 * Class to handle repeating events
 */
class Calendar_RepeatEvents
{

	/**
	 * Get timing using YYYY-MM-DD HH:MM:SS input string.
	 */
	static function mktime($fulldateString)
	{
		$splitpart = self::splittime($fulldateString);
		$datepart = explode('-', $splitpart[0]);
		$timepart = explode(':', $splitpart[1]);
		return mktime($timepart[0], $timepart[1], 0, $datepart[1], $datepart[2], $datepart[0]);
	}

	/**
	 * Increment the time by interval and return value in YYYY-MM-DD HH:MM format.
	 */
	static function nexttime($basetiming, $interval)
	{
		return date('Y-m-d H:i', strtotime($interval, $basetiming));
	}

	/**
	 * Based on user time format convert the YYYY-MM-DD HH:MM value.
	 */
	static function formattime($timeInYMDHIS)
	{
		$current_user = vglobal('current_user');
		$format_string = 'Y-m-d H:i';
		switch ($current_user->date_format) {
			case 'dd-mm-yyyy': $format_string = 'd-m-Y H:i';
				break;
			case 'mm-dd-yyyy': $format_string = 'm-d-Y H:i';
				break;
			case 'yyyy-mm-dd': $format_string = 'Y-m-d H:i';
				break;
			case 'dd.mm.yyyy': $format_string = 'd.m.Y H:i';
				break;
			case 'mm.dd.yyyy': $format_string = 'm.d.Y H:i';
				break;
			case 'yyyy.mm.dd': $format_string = 'Y.m.d H:i';
				break;
			case 'dd/mm/yyyy': $format_string = 'd/m/Y H:i';
				break;
			case 'mm/dd/yyyy': $format_string = 'm/d/Y H:i';
				break;
			case 'yyyy/mm/dd': $format_string = 'Y/m/d H:i';
				break;
		}
		return date($format_string, self::mktime($timeInYMDHIS));
	}

	/**
	 * Split full timing into date and time part.
	 */
	static function splittime($fulltiming)
	{
		return explode(' ', $fulltiming);
	}

	/**
	 * Calculate the time interval to create repeated event entries.
	 */
	static function getRepeatInterval($type, $frequency, $recurringInfo, $start_date, $limit_date)
	{
		$repeatInterval = Array();
		$starting = self::mktime($start_date);
		$limiting = self::mktime($limit_date);

		if ($type == 'Daily') {
			$count = 0;
			while (true) {
				++$count;
				$interval = ($count * $frequency);
				if (self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
					break;
				}
				$repeatInterval[] = $interval;
			}
		} else if ($type == 'Weekly') {
			if ($recurringInfo->dayofweek_to_rpt == null) {
				$count = 0;
				$weekcount = 7;
				while (true) {
					++$count;
					$interval = $count * $weekcount;
					if (self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
						break;
					}
					$repeatInterval[] = $interval;
				}
			} else {
				$count = 0;
				while (true) {
					++$count;
					$interval = $count;
					$new_timing = self::mktime(self::nexttime($starting, "+$interval days"));
					$new_timing_dayofweek = date('N', $new_timing);
					if ($new_timing > $limiting) {
						break;
					}
					if (in_array($new_timing_dayofweek - 1, $recurringInfo->dayofweek_to_rpt)) {
						$repeatInterval[] = $interval;
					}
				}
			}
		} else if ($type == 'Monthly') {
			$count = 0;
			$avg_monthcount = 30;
			while (true) {
				++$count;
				$interval = $count * $avg_monthcount;
				if (self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
					break;
				}
				$repeatInterval[] = $interval;
			}
		} else if ($type == 'Yearly') {
			$count = 0;
			$avg_monthcount = 30;
			while (true) {
				++$count;
				$interval = $count * $avg_monthcount;
				if (self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
					break;
				}
				$repeatInterval[] = $interval;
			}
		}
		return $repeatInterval;
	}

	/**
	 * Repeat Activity instance till given limit.
	 */
	static function repeat($focus, $recurObj)
	{
		$adb = PearDatabase::getInstance();
		$frequency = $recurObj->recur_freq;
		$repeattype = $recurObj->recur_type;

		$base_focus = new Activity();
		$base_focus->column_fields = $focus->column_fields;
		$base_focus->id = $focus->id;

		$skip_focus_fields = Array('record_id', 'createdtime', 'modifiedtime', 'recurringtype');

		/** Create instance before and reuse */
		$new_focus = new Activity();

		$eventStartDate = $focus->column_fields['date_start'];
		$interval = strtotime($focus->column_fields['due_date']) -
			strtotime($focus->column_fields['date_start']);

		foreach ($recurObj->recurringdates as $index => $startDate) {
			if ($index == 0 && $eventStartDate == $startDate) {
				continue;
			}
			$startDateTimestamp = strtotime($startDate);
			$endDateTime = $startDateTimestamp + $interval;
			$endDate = date('Y-m-d', $endDateTime);

			// Reset the new_focus and prepare for reuse
			if (isset($new_focus->id))
				unset($new_focus->id);
			$new_focus->column_fields = array();

			foreach ($base_focus->column_fields as $key => $value) {
				if (in_array($key, $skip_focus_fields)) {
					// skip copying few fields
				} else if ($key == 'date_start') {
					$new_focus->column_fields['date_start'] = $startDate;
				} else if ($key == 'due_date') {
					$new_focus->column_fields['due_date'] = $endDate;
				} else {
					$new_focus->column_fields[$key] = $value;
				}
			}
			if ($numberOfRepeats > 10 && $index > 10) {
				unset($new_focus->column_fields['sendnotification']);
			}
			$new_focus->save('Calendar');
		}
	}

	static function repeatFromRequest($focus)
	{
		$recurObj = \vtlib\Functions::getRecurringObjValue();
		self::repeat($focus, $recurObj);
	}
}
