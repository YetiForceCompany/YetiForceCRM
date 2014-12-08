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
require_once('include/utils/utils.php');
require_once('modules/Calendar/Date.php');
global $app_strings;

class RecurringType {

	var $recur_type;
	var $startdate;
	var $enddate;
	var $recur_freq;
	var $dayofweek_to_rpt = array();
	var $repeat_monthby;
	var $rptmonth_datevalue;
	var $rptmonth_daytype;
	var $recurringdates = array();
	var $reminder;
	var $recurringenddate; 

	/**
	 * Constructor for class RecurringType
	 * @param array  $repeat_arr     - array contains recurring info
	 */
	function RecurringType($repeat_arr) {

		$st_date = explode("-", $repeat_arr["startdate"]);
		$st_time = explode(":", $repeat_arr["starttime"]);
		$end_date = explode("-", $repeat_arr["enddate"]);
		$end_time = explode(":", $repeat_arr['endtime']);
		$recurringenddate = explode("-", $repeat_arr["recurringenddate"]); 

		$start_date = Array(
			'day' => $st_date[2],
			'month' => $st_date[1],
			'year' => $st_date[0],
			'hour' => $st_time[0],
			'min' => $st_time[1]
		);
		$end_date = Array(
			'day' => $end_date[2],
			'month' => $end_date[1],
			'year' => $end_date[0],
			'hour' => $end_time[0],
			'min' => $end_time[1]
		);
		$recurringenddate = Array( 
			'day' => $recurringenddate[2], 
			'month' => $recurringenddate[1], 
			'year' => $recurringenddate[0], 
		); 
		$this->startdate = new vt_DateTime($start_date, true);
		$this->enddate = new vt_DateTime($end_date, true);
		$this->recurringenddate = new vt_DateTime($recurringenddate, true); 

		$this->recur_type = $repeat_arr['type'];
		$this->recur_freq = $repeat_arr['repeat_frequency'];
		if (empty($this->recur_freq)) {
			$this->recur_freq = 1;
		}
		$this->dayofweek_to_rpt = $repeat_arr['dayofweek_to_repeat'];
		$this->repeat_monthby = $repeat_arr['repeatmonth_type'];
		if (isset($repeat_arr['repeatmonth_date']))
			$this->rptmonth_datevalue = $repeat_arr['repeatmonth_date'];
		$this->rptmonth_daytype = $repeat_arr['repeatmonth_daytype'];
		
		$this->recurringdates = $this->_getRecurringDates();
	}

	public static function fromUserRequest($requestArray) {
		// All the information from the user is received in User Time zone
		// Convert Start date and Time to DB Time zone
		$startDateObj = DateTimeField::convertToDBTimeZone($requestArray["startdate"] . ' ' . $requestArray['starttime']);
		$requestArray['startdate'] = $startDate = $startDateObj->format('Y-m-d');
		$requestArray['starttime'] = $startTime = $startDateObj->format('H:i');
		$endDateObj = DateTimeField::convertToDBTimeZone($requestArray["enddate"] . ' ' . $requestArray['endtime']);
		$requestArray['enddate'] = $endDate = $endDateObj->format('Y-m-d');
		$requestArray['endtime'] = $endTime = $endDateObj->format('H:i');
		if(!empty($requestArray["recurringenddate"])){ 
			$reccurringDateObj = DateTimeField::convertToDBTimeZone($requestArray["recurringenddate"] . ' ' . $requestArray['endtime']); 
			$requestArray['recurringenddate'] = $reccurringDateObj->format('Y-m-d'); 
		} 

		if ($requestArray['sun_flag']) {
			$requestArray['dayofweek_to_repeat'][] = 0;
		}
		if ($requestArray['mon_flag']) {
			$requestArray['dayofweek_to_repeat'][] = 1;
		}
		if ($requestArray['tue_flag']) {
			$requestArray['dayofweek_to_repeat'][] = 2;
		}
		if ($requestArray['wed_flag']) {
			$requestArray['dayofweek_to_repeat'][] = 3;
		}
		if ($requestArray['thu_flag']) {
			$requestArray['dayofweek_to_repeat'][] = 4;
		}
		if ($requestArray['fri_flag']) {
			$requestArray['dayofweek_to_repeat'][] = 5;
		}
		if ($requestArray['sat_flag']) {
			$requestArray['dayofweek_to_repeat'][] = 6;
		}

		if ($requestArray['type'] == 'Weekly') {
			if ($requestArray['dayofweek_to_repeat'] != null) {
				$userStartDateTime = DateTimeField::convertToUserTimeZone($startDate . ' ' . $startTime);
				$dayOfWeek = $requestArray['dayofweek_to_repeat'];
				$dbDaysOfWeek = array();
				for ($i = 0; $i < count($dayOfWeek); ++$i) {
					$selectedDayOfWeek = $dayOfWeek[$i];
					$currentDayOfWeek = $userStartDateTime->format('w');
					$newDate = $userStartDateTime->format('d') + ($selectedDayOfWeek - $currentDayOfWeek);
					$userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $newDate);
					$dbDaysOfWeek[] = $userStartDateTime->format('w');
				}
				$requestArray['dayofweek_to_repeat'] = $dbDaysOfWeek;
			}
		} elseif ($requestArray['type'] == 'Monthly') {
			$userStartDateTime = DateTimeField::convertToUserTimeZone($startDate . ' ' . $startTime);
			if ($requestArray['repeatmonth_type'] == 'date') {
				$dayOfMonth = $requestArray['repeatmonth_date'];
				$userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $dayOfMonth);
				$userStartDateTime->setTimezone(new DateTimeZone(DateTimeField::getDBTimeZone()));
				$requestArray['repeatmonth_date'] = $userStartDateTime->format('d');
			} else {
				$dayOfWeek = $requestArray['dayofweek_to_repeat'][0];
				if ($requestArray['repeatmonth_daytype'] == 'first') {
					$userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), 1);
					$dayOfWeekForFirstDay = $userStartDateTime->format('N');
					if ($dayOfWeekForFirstDay < $dayOfWeek) {
						$date = $dayOfWeek - $dayOfWeekForFirstDay + 1;
					} else {
						$date = (7 - $dayOfWeekForFirstDay) + $dayOfWeek + 1;
					}
				} elseif ($requestArray['repeatmonth_daytype'] == 'last') {
					$daysInMonth = $userStartDateTime->format('t');
					$userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $daysInMonth);
					$dayOfWeekForLastDay = $userStartDateTime->format('N');
					if ($dayOfWeekForLastDay < $dayOfWeek) {
						$date = $daysInMonth - 7 + ($dayOfWeek - $dayOfWeekForLastDay);
					} else {
						$date = $daysInMonth - ($dayOfWeekForLastDay - $dayOfWeek);
					}
				}
				$userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $date);
				$userStartDateTime->setTimezone(new DateTimeZone(DateTimeField::getDBTimeZone()));
				$requestArray['dayofweek_to_repeat'][0] = (int)$userStartDateTime->format('N')%7;
			}
		}

		return new RecurringType($requestArray);
	}

	public static function fromDBRequest($resultRow) {
		// All the information from the database is received in DB Time zone
		
		$repeatInfo = array();

		$repeatInfo['startdate'] = $startDate = $resultRow['date_start'];
		$repeatInfo['starttime'] = $startTime = $resultRow['time_start'];
		$repeatInfo['enddate'] = $endDate = $resultRow['due_date'];
		$repeatInfo['endtime'] = $endTime = $resultRow['time_end'];

		$repeatInfo['type'] = $resultRow['recurringtype'];
		$repeatInfo['repeat_frequency'] = $resultRow['recurringfreq'];
		$repeatInfo['recurringenddate'] = $resultRow['recurringenddate']; 

		$recurringInfoString = $resultRow['recurringinfo'];
		$recurringInfo = explode('::', $recurringInfoString);

		if ($repeatInfo['type'] == 'Weekly') {
			$startIndex = 1; // 0 is for Recurring Type
			$length = count($recurringInfo);
			$j = 0;
			for ($i = $startIndex; $i < $length; ++$i) {
				$repeatInfo['dayofweek_to_repeat'][$j++] = $recurringInfo[$i];
			}
		} elseif ($repeatInfo['type'] == 'Monthly') {
			$repeatInfo['repeatmonth_type'] = $recurringInfo[1];
			if ($repeatInfo['repeatmonth_type'] == 'date') {
				$repeatInfo['repeatmonth_date'] = $recurringInfo[2];
			} else {
				$repeatInfo['repeatmonth_daytype'] = $recurringInfo[2];
				$repeatInfo['dayofweek_to_repeat'][0] = $recurringInfo[3];
			}
		}

		return new RecurringType($repeatInfo);
	}

	function getRecurringType() {
		return $this->recur_type;
	}

	function getRecurringFrequency() {
		return $this->recur_freq;
	}
	function getRecurringEndDate() { 
		return $this->recurringenddate; 
	} 

	function getDBRecurringInfoString() {
		$recurringType = $this->getRecurringType();
		$recurringInfo = '';
		if ($recurringType == 'Daily' || $recurringType == 'Yearly') {
			$recurringInfo = $recurringType;
		} elseif ($recurringType == 'Weekly') {
			if ($this->dayofweek_to_rpt != null) {
				$recurringInfo = $recurringType . '::' . implode('::', $this->dayofweek_to_rpt);
			} else {
				$recurringInfo = $recurringType;
			}
		} elseif ($recurringType == 'Monthly') {
			$recurringInfo = $recurringType . '::' . $this->repeat_monthby;
			if ($this->repeat_monthby == 'date') {
				$recurringInfo = $recurringInfo . '::' . $this->rptmonth_datevalue;
			} else {
				$recurringInfo = $recurringInfo . '::' . $this->rptmonth_daytype . '::' . $this->dayofweek_to_rpt[0];
			}
		}

		return $recurringInfo;
	}

	function getUserRecurringInfo() {
		$recurringType = $this->getRecurringType();
		$recurringInfo = array();

		if ($recurringType == 'Weekly') {
			if ($this->dayofweek_to_rpt != null) {
				$recurringInfo['dayofweek_to_repeat'] = $this->dayofweek_to_rpt;
			}
		} elseif ($recurringType == 'Monthly') {
			$dbStartDateTime = new DateTime($this->startdate->get_DB_formatted_date() . ' ' . $this->startdate->get_formatted_time());
			$recurringInfo['repeatmonth_type'] = $this->repeat_monthby;
			if ($this->repeat_monthby == 'date') {
				$dayOfMonth = $this->rptmonth_datevalue;
				$dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), $dayOfMonth);
				$userStartDateTime = DateTimeField::convertToUserTimeZone($dbStartDateTime->format('Y-m-d') . ' ' . $dbStartDateTime->format('H:i'));
				$recurringInfo['repeatmonth_date'] = $userStartDateTime->format('d');
			} else {
				$dayOfWeek = $this->dayofweek_to_rpt[0];
				$recurringInfo['repeatmonth_daytype'] = $this->rptmonth_daytype;
				if ($this->rptmonth_daytype == 'first') {
					$dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), 1);
					$dayOfWeekForFirstDay = $dbStartDateTime->format('N');
					if ($dayOfWeekForFirstDay < $dayOfWeek) {
						$date = $dayOfWeek - $dayOfWeekForFirstDay + 1;
					} else {
						$date = (7 - $dayOfWeekForFirstDay) + $dayOfWeek + 1;
					}
				} elseif ($this->rptmonth_daytype == 'last') {
					$daysInMonth = $dbStartDateTime->format('t');
					$dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), $daysInMonth);
					$dayOfWeekForLastDay = $dbStartDateTime->format('N');
					if ($dayOfWeekForLastDay < $dayOfWeek) {
						$date = $daysInMonth - 7 + ($dayOfWeek - $dayOfWeekForLastDay);
					} else {
						$date = $daysInMonth - ($dayOfWeekForLastDay - $dayOfWeek);
					}
				}
				$dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), $date);
				$userStartDateTime = DateTimeField::convertToUserTimeZone($dbStartDateTime->format('Y-m-d') . ' ' . $dbStartDateTime->format('H:i'));
				$recurringInfo['dayofweek_to_repeat'][0] = (int)$userStartDateTime->format('N')%7;
			}
		}
		return $recurringInfo;
	}

	function getDisplayRecurringInfo() {
		global $currentModule;

		$displayRecurringData = array();

		$recurringInfo = $this->getUserRecurringInfo();

		$displayRecurringData['recurringcheck'] = getTranslatedString('LBL_YES', $currentModule);
		$displayRecurringData['repeat_frequency'] = $this->getRecurringFrequency();
		$displayRecurringData['recurringtype'] = $this->getRecurringType();

		if ($this->getRecurringType() == 'Weekly') {
			$noOfDays = count($recurringInfo['dayofweek_to_repeat']);
			$translatedRepeatDays = array();
			for ($i = 0; $i < $noOfDays; ++$i) {
				$translatedRepeatDays[] = getTranslatedString('LBL_DAY' . $recurringInfo['dayofweek_to_repeat'][$i], $currentModule);
			}
			$displayRecurringData['repeat_str'] = getTranslatedString('On', $currentModule).' '.implode(',', $translatedRepeatDays);
		} elseif ($this->getRecurringType() == 'Monthly') {

			$translatedRepeatDays = array();
			$displayRecurringData['repeatMonth'] = $recurringInfo['repeatmonth_type'];
			if ($recurringInfo['repeatmonth_type'] == 'date') {
				$displayRecurringData['repeatMonth_date'] = $recurringInfo['repeatmonth_date'];
				$displayRecurringData['repeat_str'] = getTranslatedString('on', $currentModule)
						. ' ' . $recurringInfo['repeatmonth_date']
						. ' ' . getTranslatedString('day of the month', $currentModule);
			} else {
				$displayRecurringData['repeatMonth_daytype'] = $recurringInfo['repeatmonth_daytype'];
				$displayRecurringData['repeatMonth_day'] = $recurringInfo['dayofweek_to_repeat'][0];
				$translatedRepeatDay = getTranslatedString('LBL_DAY' . $recurringInfo['dayofweek_to_repeat'][0], $currentModule);

				$displayRecurringData['repeat_str'] = getTranslatedString('On', $currentModule)
						. ' ' . getTranslatedString($recurringInfo['repeatmonth_daytype'], $currentModule)
						. ' ' . $translatedRepeatDay;
			}
		}

		return $displayRecurringData;
	}

	/**
	 *  Function to get recurring dates depending on the recurring type
	 *  return  array   $recurringDates     -  Recurring Dates in format
	 * 	Recurring date will be returned in DB Time Zone, as well as DB format
	 */
	function _getRecurringDates() {
		$startdateObj = $this->startdate;
		$startdate = $startdateObj->get_DB_formatted_date();
		$recurringDates[] = $startdate;
		$tempdateObj = $startdateObj;
		$tempdate = $startdate;
		$enddate = $this->enddate->get_DB_formatted_date();

		$dbDateTime = strtotime($startdate);
		$userDateTime = strtotime($startdateObj->get_userTimezone_formatted_date());
		$dateDiff = $dbDateTime - $userDateTime;
		if ($dateDiff < 0) {
			$dayDiff = $dateDiff/3600/24;
		} elseif ($dateDiff > 0) {
			$dayDiff = $dateDiff/3600/24;
		}

		while ($tempdate <= $enddate) {
			$date = $tempdateObj->get_Date();
			$month = $tempdateObj->getMonth();
			$year = $tempdateObj->getYear();

			if ($this->recur_type == 'Daily') {
				if (isset($this->recur_freq)) {
					$index = $date + $this->recur_freq - 1;
				} else {
					$index = $date;
				}
				$tempdateObj = $this->startdate->getThismonthDaysbyIndex($index, '', $month, $year);
				$tempdate = $tempdateObj->get_DB_formatted_date();
				if($tempdate <= $enddate) {
					$recurringDates[] = $tempdate;
				}
			} elseif ($this->recur_type == 'Weekly') {
				if (count($this->dayofweek_to_rpt) == 0) {
					$this->dayofweek_to_rpt[] = $this->startdate->dayofweek;
				}

				for ($i = 0; $i < count($this->dayofweek_to_rpt); $i++) {
					$repeat = $this->dayofweek_to_rpt[$i];
					if ($repeat == 0) {
						$repeat = $repeat+1;
						$isSunday = true;
					}
					$repeatDay = $tempdateObj->getThisweekDaysbyIndex($repeat);
					$repeatDate = $repeatDay->get_DB_formatted_date();
					if ($dayDiff) {
						$repeatDate = date('Y-m-d', strtotime($dayDiff.' day', strtotime($repeatDate)));
					}
					if ($isSunday) {
						$repeatDate = date('Y-m-d', strtotime('-1 day', strtotime($repeatDate)));
						$isSunday = false;
					}
					if ($repeatDate > $startdate && $repeatDate <= $enddate) {
						$recurringDates[] = $repeatDate;
					}
				}

				if (isset($this->recur_freq)) {
					$index = $this->recur_freq * 7;
				} else {
					$index = 7;
				}
				$date_arr = Array(
					'day' => $date + $index,
					'month' => $month,
					'year' => $year
				);
				$tempdateObj = new vt_DateTime($date_arr, true);
				$tempdate = $tempdateObj->get_DB_formatted_date();
			} elseif ($this->recur_type == 'Monthly') {
				if ($this->repeat_monthby == 'date') {
					if ($this->rptmonth_datevalue <= $date) {
						$index = $this->rptmonth_datevalue - 1;
						$day = $this->rptmonth_datevalue;
						if (isset($this->recur_freq))
							$month = $month + $this->recur_freq;
						else
							$month = $month + 1;
						$tempdateObj = $tempdateObj->getThismonthDaysbyIndex($index, $day, $month, $year);
					}
					else {
						$index = $this->rptmonth_datevalue - 1;
						$day = $this->rptmonth_datevalue;
						$tempdateObj = $tempdateObj->getThismonthDaysbyIndex($index, $day, $month, $year);
					}
				} elseif ($this->repeat_monthby == 'day') {
					if ($this->rptmonth_daytype == 'first') {
						$date_arr = Array(
							'day' => 1,
							'month' => $month,
							'year' => $year
						);
						$tempdateObj = new vt_DateTime($date_arr, true);
						$firstdayofmonthObj = $this->getFistdayofmonth($this->dayofweek_to_rpt[0], $tempdateObj);
						if ($firstdayofmonthObj->get_DB_formatted_date() <= $tempdate) {
							if (isset($this->recur_freq))
								$month = $firstdayofmonthObj->getMonth() + $this->recur_freq;
							else
								$month = $firstdayofmonthObj->getMonth() + 1;
							$dateObj = $firstdayofmonthObj->getThismonthDaysbyIndex(0, 1, $month, $firstdayofmonthObj->getYear());
							$nextmonthObj = $this->getFistdayofmonth($this->dayofweek_to_rpt[0], $dateObj);
							$tempdateObj = $nextmonthObj;
						} else {
							$tempdateObj = $firstdayofmonthObj;
						}
					} elseif ($this->rptmonth_daytype == 'last') {
						$date_arr = Array(
							'day' => $tempdateObj->getDaysInMonth(),
							'month' => $tempdateObj->getMonth(),
							'year' => $tempdateObj->getYear()
						);
						$tempdateObj = new vt_DateTime($date_arr, true);
						$lastdayofmonthObj = $this->getLastdayofmonth($this->dayofweek_to_rpt[0], $tempdateObj);
						if ($lastdayofmonthObj->get_DB_formatted_date() <= $tempdate) {
							if (isset($this->recur_freq))
								$month = $lastdayofmonthObj->getMonth() + $this->recur_freq;
							else
								$month = $lastdayofmonthObj->getMonth() + 1;
							$dateObj = $lastdayofmonthObj->getThismonthDaysbyIndex(0, 1, $month, $lastdayofmonthObj->getYear());
							$dateObj = $dateObj->getThismonthDaysbyIndex($dateObj->getDaysInMonth() - 1,
											$dateObj->getDaysInMonth(), $month, $lastdayofmonthObj->getYear());
							$nextmonthObj = $this->getLastdayofmonth($this->dayofweek_to_rpt[0], $dateObj);
							$tempdateObj = $nextmonthObj;
						}
						else {
							$tempdateObj = $lastdayofmonthObj;
						}
					}
				} else {
					$date_arr = Array(
						'day' => $date,
						'month' => $month + 1,
						'year' => $year
					);
					$tempdateObj = new vt_DateTime($date_arr, true);
				}
				$tempdate = $tempdateObj->get_DB_formatted_date();
				if ($tempdate <= $enddate) {
					$recurringDates[] = $tempdate;
				}
			} elseif ($this->recur_type == 'Yearly') {

				if (isset($this->recur_freq))
					$index = $year + $this->recur_freq;
				else
					$index = $year + 1;
				if ($index > 2037 || $index < 1970) {
					print("<font color='red'>" . $app_strings['LBL_CAL_LIMIT_MSG'] . "</font>");
					exit;
				}
				$date_arr = Array(
					'day' => $date,
					'month' => $month,
					'year' => $index
				);
				$tempdateObj = new vt_DateTime($date_arr, true);
				$tempdate = $tempdateObj->get_DB_formatted_date();
				if ($tempdate <= $enddate) {
					$recurringDates[] = $tempdate;
				}
			} else {
				die("Recurring Type " . $this->recur_type . " is not defined");
			}
		}
		return $recurringDates;
	}

	/** Function to get first day of the month(like first Monday or Friday and etc.)
	 *  @param $dayofweek   -- day of the week to repeat the event :: Type string
	 *  @param $dateObj     -- date object  :: Type vt_DateTime Object
	 *  return $dateObj -- the date object on which the event repeats :: Type vt_DateTime Object
	 */
	function getFistdayofmonth($dayofweek, & $dateObj) {
		if ($dayofweek < $dateObj->dayofweek) {
			$index = (7 - $dateObj->dayofweek) + $dayofweek;
			$day = 1 + $index;
			$month = $dateObj->month;
			$year = $dateObj->year;
			$dateObj = $dateObj->getThismonthDaysbyIndex($index, $day, $month, $year);
		} else {
			$index = $dayofweek - $dateObj->dayofweek;
			$day = 1 + $index;
			$month = $dateObj->month;
			$year = $dateObj->year;
			$dateObj = $dateObj->getThismonthDaysbyIndex($index, $day, $month, $year);
		}
		return $dateObj;
	}

	/** Function to get last day of the month(like last Monday or Friday and etc.)
	 *  @param $dayofweek   -- day of the week to repeat the event :: Type string
	 *  @param $dateObj     -- date object  :: Type vt_DateTime Object
	 *  return $dateObj -- the date object on which the event repeats :: Type vt_DateTime Object
	 */
	function getLastdayofmonth($dayofweek, & $dateObj) {
		if ($dayofweek == $dateObj->dayofweek) {
			return $dateObj;
		} else {
			if ($dayofweek > $dateObj->dayofweek)
				$day = $dateObj->day - 7 + ($dayofweek - $dateObj->dayofweek);
			else
				$day = $dateObj->day - ($dateObj->dayofweek - $dayofweek);
			$index = $day - 1;
			$month = $dateObj->month;
			$year = $dateObj->year;
			$dateObj = $dateObj->getThismonthDaysbyIndex($index, $day, $month, $year);
			return $dateObj;
		}
	}

}

?>
