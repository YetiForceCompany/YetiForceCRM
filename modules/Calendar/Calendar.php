<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('modules/Calendar/CalendarCommon.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Calendar/Activity.php');
require_once('modules/Calendar/Date.php');

class Calendar
{

	public $view = 'day';
	public $date_time;
	public $hour_format = 12;
	public $day_slice;
	public $week_slice;
	public $week_array;
	public $month_array;
	public $week_hour_slices = Array();
	public $slices = Array();
	/* for dayview */
	public $day_start_hour = 0;
	public $day_end_hour = 23;
	public $sharedusers = Array();

	/*
	  constructor
	 */

	public function __construct($view = '', $data = Array())
	{
		$this->view = $view;
		$this->date_time = new vt_DateTime($data, true);
		$this->constructLayout();
	}

	/**
	 * Function to get calendarview Label
	 * @param string  $view   - calendarview
	 * return string  - calendarview Label 
	 */
	public function getCalendarView($view)
	{
		switch ($view) {
			case 'day':
				return "DAY";
			case 'week':
				return "WEEK";
			case 'month':
				return "MON";
			case 'year':
				return "YEAR";
		}
	}

	/**
	 * Function to set values for calendar object depends on calendar view
	 */
	public function constructLayout()
	{
		$current_user = vglobal('current_user');
		switch ($this->view) {
			case 'day':
				for ($i = -1; $i <= 23; $i++) {
					if ($i == -1) {
						$layout = new Layout('hour', $this->date_time->getTodayDatetimebyIndex(0));
						$this->day_slice[$layout->start_time->get_formatted_date() . ':notime'] = $layout;
						$this->slices['notime'] = $layout->start_time->get_formatted_date() . ":notime";
					} else {
						$layout = new Layout('hour', $this->date_time->getTodayDatetimebyIndex($i));
						$this->day_slice[$layout->start_time->get_formatted_date() . ':' . $layout->start_time->z_hour] = $layout;
						array_push($this->slices, $layout->start_time->get_formatted_date() . ":" . $layout->start_time->z_hour);
					}
				}
				break;
			case 'week':
				$weekview_days = 7;
				for ($i = 1; $i <= $weekview_days; $i++) {
					$layout = new Layout('day', $this->date_time->getThisweekDaysbyIndex($i));
					$this->week_array[$layout->start_time->get_formatted_date()] = $layout;

					for ($h = -1; $h <= 23; $h++) {
						if ($h == -1) {
							$hour_list = new Layout('hour', $this->date_time->getTodayDatetimebyIndex(0, $layout->start_time->day, $layout->start_time->month, $layout->start_time->year));
							$this->week_slice[$layout->start_time->get_formatted_date() . ':notime'] = $hour_list;
							$this->week_hour_slices['notime'] = $layout->start_time->get_formatted_date() . ":notime";
						} else {
							$hour_list = new Layout('hour', $this->date_time->getTodayDatetimebyIndex($h, $layout->start_time->day, $layout->start_time->month, $layout->start_time->year));
							$this->week_slice[$layout->start_time->get_formatted_date() . ':' . $hour_list->start_time->z_hour] = $hour_list;
							array_push($this->week_hour_slices, $layout->start_time->get_formatted_date() . ":" . $hour_list->start_time->z_hour);
						}
					}
					array_push($this->slices, $layout->start_time->get_formatted_date());
				}
				break;
			case 'month':
				$arr = getCalendarDaysInMonth($this->date_time);
				$this->month_array = $arr["month_array"];
				$this->slices = $arr["slices"];
				$this->date_time = $arr["date_time"];
				break;
			case 'year':
				$this->month_day_slices = Array();
				for ($i = 0; $i < 12; $i++) {
					$currMonth = $this->date_time->getThisyearMonthsbyIndex($i);
					$layout = new Layout('month', $this->date_time->getThisyearMonthsbyIndex($i));
					$this->year_array[$layout->start_time->z_month] = $layout;

					$arr = getCalendarDaysInMonth($currMonth);
					$slices = $arr["slices"];

					$this->month_day_slices[$i] = $slices;
					array_push($this->slices, $layout->start_time->z_month);
				}
				break;
		}
	}

	/**
	 * Function to get date info depends on calendarview
	 * @param  string   $type  - string 'increment' or 'decrment'
	 */
	public function get_datechange_info($type)
	{
		if ($type == 'next')
			$mode = 'increment';
		if ($type == 'prev')
			$mode = 'decrment';
		switch ($this->view) {
			case 'day':
				$day = $this->date_time->get_changed_day($mode);
				break;
			case 'week':
				$day = $this->date_time->get_first_day_of_changed_week($mode);
				break;
			case 'month':
				$day = $this->date_time->get_first_day_of_changed_month($mode);
				break;
			case 'year':
				$day = $this->date_time->get_first_day_of_changed_year($mode);
				break;
			default:
				return "view is not supported";
		}
		return $day->get_date_str();
	}
}

class Layout
{

	public $view = 'day';
	public $start_time;
	public $end_time;
	public $activities = Array();

	/**
	 * Constructor for Layout class
	 * @param  string   $view - calendarview
	 * @param  string   $time - time string 
	 */
	public function Layout($view, $time)
	{
		$this->view = $view;
		$this->start_time = $time;
		if ($view == 'month')
			$this->end_time = $this->start_time->getMonthendtime();
		if ($view == 'day')
			$this->end_time = $this->start_time->getDayendtime();
		if ($view == 'hour')
			$this->end_time = $this->start_time->getHourendtime();
	}

	/**
	 * Function to get view 
	 * return currentview
	 */
	public function getView()
	{
		return $this->view;
	}
}

/**
 * this function returns the days in a month in an array format
 * @param object $date_time - the date time object for the current month
 * @return array $result - the array containing current months days information
 */
function getCalendarDaysInMonth($date_time)
{
	$current_user = vglobal('current_user');
	$month_array = array();
	$slices = array();
	$monthview_days = $date_time->daysinmonth;

	$firstday_of_month = $date_time->getThisMonthsDayByIndex(0);
	$fdom = $firstday_of_month;

	$num_of_prev_days = ($fdom->dayofweek + 7) % 7 - 1;
	for ($i = -$num_of_prev_days; $i < 42; $i++) {
		$pd = $date_time->getThisMonthsDayByIndex($i);

		$layout = new Layout('day', $pd);
		$date = $layout->start_time->get_formatted_date();
		$month_array[$date] = $layout;
		array_push($slices, $date);
	}

	$result = array("month_array" => $month_array, "slices" => $slices, "date_time" => $date_time);
	return $result;
}
