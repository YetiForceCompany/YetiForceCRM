<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

require_once 'include/utils/utils.php';

class DateTimeRange
{

	/** Function that converts string to dates
	 * @param $type :: type string
	 * @returns  $dateValue array in the following format
	 *           $dateValue = Array(0=>$startdate,1=>$enddate)
	 */
	public static function getDateRangeByType($type, &$dateObject = null)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$weekStartDay = $currentUser->get('dayoftheweek');

		if (!$dateObject) {
			$timeZone = $timezone = new DateTimeZone($currentUser->get('time_zone'));
			$dateObject = new DateTime();
			$dateObject->setTimezone($timeZone);
		} else if (is_string($dateObject)) {
			$dateObject = new DateTime($dateObject);
		}
		$thisMonth = $dateObject->format('m');

		$today = $dateObject->format('Y-m-d');
		$todayName = $dateObject->format('l');

		switch ($type) {
			case 'today':
				$dateValue[0] = $today;
				$dateValue[1] = $today;
				break;

			case 'yesterday':
				$dateObject->modify('last day');
				$yesterday = $dateObject->format('Y-m-d');
				$dateValue[0] = $yesterday;
				$dateValue[1] = $yesterday;
				break;

			case 'tomorrow':
				$dateObject->modify('tomorrow');
				$tomorrow = $dateObject->format('Y-m-d');
				$dateValue[0] = $tomorrow;
				$dateValue[1] = $tomorrow;
				break;

			case 'thisweek':
				if ($todayName == $weekStartDay) {
					$dateObject->modify('-0 week ' . $weekStartDay);
				} else {
					$dateObject->modify('-1 week ' . $weekStartDay);
				}
				$thisWeekStart = $dateObject->format('Y-m-d');
				$dateObject->modify('+6 days');
				$thisWeekEnd = $dateObject->format('Y-m-d');
				$dateValue[0] = $thisWeekStart;
				$dateValue[1] = $thisWeekEnd;
				break;

			case 'lastweek':
				if ($todayName == $weekStartDay) {
					$dateObject->modify('-1 week ' . $weekStartDay);
				} else {
					$dateObject->modify('-2 week ' . $weekStartDay);
				}
				$lastWeekStart = $dateObject->format('Y-m-d');
				$dateObject->modify('+6 days');
				$lastWeekEnd = $dateObject->format('Y-m-d');
				$dateValue[0] = $lastWeekStart;
				$dateValue[1] = $lastWeekEnd;
				break;

			case 'nextweek':
				if ($todayName == $weekStartDay) {
					$dateObject->modify('+1 week ' . $weekStartDay);
				} else {
					$dateObject->modify('this ' . $weekStartDay);
				}
				$nextWeekStart = $dateObject->format('Y-m-d');
				$dateObject->modify('+6 days');
				$nextWeekEnd = $dateObject->format('Y-m-d');
				$dateValue[0] = $nextWeekStart;
				$dateValue[1] = $nextWeekEnd;
				break;

			case 'thismonth':
				$currentMonthStart = $dateObject->format('Y-m-01');
				$currentMonthEnd = $dateObject->format('Y-m-t');
				$dateValue[0] = $currentMonthStart;
				$dateValue[1] = $currentMonthEnd;
				break;

			case 'lastmonth':
				$dateObject->modify('last month');
				$lastMonthStart = $dateObject->format('Y-m-01');
				$lastMonthEnd = $dateObject->format('Y-m-t');
				$dateValue[0] = $lastMonthStart;
				$dateValue[1] = $lastMonthEnd;
				break;

			case 'nextmonth':
				$dateObject->modify('next month');
				$nextMonthStart = $dateObject->format('Y-m-01');
				$nextMonthEnd = $dateObject->format('Y-m-t');
				$dateValue[0] = $nextMonthStart;
				$dateValue[1] = $nextMonthEnd;
				break;

			case 'next7days':
				$dateObject->modify('+6 days');
				$next7days = $dateObject->format('Y-m-d');
				$dateValue[0] = $today;
				$dateValue[1] = $next7days;
				break;

			case 'next30days':
				$dateObject->modify('+29 days');
				$next30days = $dateObject->format('Y-m-d');
				$dateValue[0] = $today;
				$dateValue[1] = $next30days;
				break;

			case 'next60days':
				$dateObject->modify('+59 days');
				$next60days = $dateObject->format('Y-m-d');
				$dateValue[0] = $today;
				$dateValue[1] = $next60days;
				break;

			case 'next90days':
				$dateObject->modify('+89 days');
				$next90days = $dateObject->format('Y-m-d');
				$dateValue[0] = $today;
				$dateValue[1] = $next90days;
				break;

			case 'next120days':
				$dateObject->modify('+119 days');
				$next120days = $dateObject->format('Y-m-d');
				$dateValue[0] = $today;
				$dateValue[1] = $next120days;
				break;

			case 'last7days':
				$dateObject->modify('-6 days');
				$last7days = $dateObject->format('Y-m-d');
				$dateValue[0] = $last7days;
				$dateValue[1] = $today;
				break;

			case 'last30days':
				$dateObject->modify('-29 days');
				$last30days = $dateObject->format('Y-m-d');
				$dateValue[0] = $last30days;
				$dateValue[1] = $today;
				break;

			case 'last60days':
				$dateObject->modify('-59 days');
				$last60days = $dateObject->format('Y-m-d');
				$dateValue[0] = $last60days;
				$dateValue[1] = $today;
				break;

			case 'last90days':
				$dateObject->modify('-89 days');
				$last90days = $dateObject->format('Y-m-d');
				$dateValue[0] = $last90days;
				$dateValue[1] = $today;
				break;

			case 'last120days':
				$dateObject->modify('-119 days');
				$last120days = $dateObject->format('Y-m-d');
				$dateValue[0] = $last120days;
				$dateValue[1] = $today;
				break;

			case 'thisfy':
				$dateValue = self::getPresentYearRange($dateObject);
				break;

			case 'prevfy':
				$dateValue = self::getPreviousYearRange($dateObject);
				break;

			case 'nextfy':
				$dateValue = self::getNextYearRange($dateObject);
				break;

			case 'nextfq':
				$dateValue = self::getNextQuarterRange($thisMonth, $dateObject);
				break;

			case 'prevfq':
				$dateValue = self::getPreviousQuarterRange($thisMonth, $dateObject);
				break;

			case 'thisfq':
				$dateValue = self::getPresentQuarterRange($thisMonth, $dateObject);
				break;

			default:
				$dateValue[0] = '';
				$dateValue[1] = '';
		}

		return $dateValue;
	}

	/**
	 * Function to get start and end date of present calendar year
	 * @param DateTime/String $dateObject - date object or string
	 * @return date range of present year
	 */
	public static function getPresentYearRange(&$dateObject = null)
	{
		if (!$dateObject) {
			$dateObject = new DateTime();
		} else if (is_string($dateObject)) {
			$dateObject = new DateTime($dateObject);
		}

		return array($dateObject->format('Y-01-01'), $dateObject->format('Y-12-31'));
	}

	/**
	 * Function to get start and end date of next calendar year
	 * @param DateTime/String $dateObject - date object or string
	 * @return date range of next year
	 */
	public static function getNextYearRange(&$dateObject = null)
	{
		if (!$dateObject) {
			$dateObject = new DateTime();
		} else if (is_string($dateObject)) {
			$dateObject = new DateTime($dateObject);
		}
		$dateObject->modify('next year');

		return array($dateObject->format('Y-01-01'), $dateObject->format('Y-12-31'));
	}

	/**
	 * Function to get start and end date of past calendar year
	 * @param DateTime/String $dateObject - date object or string
	 * @return date range of past year
	 */
	public static function getPreviousYearRange(&$dateObject = null)
	{
		if (!$dateObject) {
			$dateObject = new DateTime();
		} else if (is_string($dateObject)) {
			$dateObject = new DateTime($dateObject);
		}
		$dateObject->modify('last year');

		return array($dateObject->format('Y-01-01'), $dateObject->format('Y-12-31'));
	}

	/**
	 * Function to get start and end date of present calendar quarter
	 * @param int $month
	 * @param DateTime $dateObject
	 * @return date range of present quarter
	 */
	public static function getPresentQuarterRange($month = null, &$dateObject = null)
	{
		$quarter = [];
		if (!$month) {
			$month = date('n');
		}
		if (!$dateObject) {
			$dateObject = new DateTime();
		}

		if ($month <= 3) { // 1st Quarter - January - March
			$quarter[0] = $dateObject->format('Y-01-01');
			$quarter[1] = $dateObject->format('Y-03-31');
		} else if ($month > 3 && $month <= 6) { // 2nd Quarter - April - June
			$quarter[0] = $dateObject->format('Y-04-01');
			$quarter[1] = $dateObject->format('Y-06-30');
		} else if ($month > 6 && $month <= 9) { // 3rd Quarter - July - September
			$quarter[0] = $dateObject->format('Y-07-01');
			$quarter[1] = $dateObject->format('Y-09-30');
		} else { // 4th Quarter - October - December
			$quarter[0] = $dateObject->format('Y-10-01');
			$quarter[1] = $dateObject->format('Y-12-31');
		}

		return $quarter;
	}

	/**
	 * Function to get start and end date of previous calendar quarter
	 * @param int $month
	 * @param DateTime $dateObject
	 * @return date range of present quarter
	 */
	public static function getPreviousQuarterRange($month = null, &$dateObject = null)
	{
		$quarter = [];
		if (!$month) {
			$month = date('n');
		}
		if (!$dateObject) {
			$dateObject = new DateTime();
		}

		if ($month <= 3) { // 1st Quarter - January - March
			$dateObject->modify('last year');
			$quarter[0] = $dateObject->format('Y-10-01');
			$quarter[1] = $dateObject->format('Y-12-31');
		} else if ($month > 3 && $month <= 6) { // 2nd Quarter - April - June
			$quarter[0] = $dateObject->format('Y-01-01');
			$quarter[1] = $dateObject->format('Y-03-31');
		} else if ($month > 6 && $month <= 9) { // 3rd Quarter - July - September
			$quarter[0] = $dateObject->format('Y-04-01');
			$quarter[1] = $dateObject->format('Y-06-30');
		} else { // 4th Quarter - October - December
			$quarter[0] = $dateObject->format('Y-07-01');
			$quarter[1] = $dateObject->format('Y-09-30');
		}

		return $quarter;
	}

	/**
	 * Function to get start and end date of next calendar quarter
	 * @param int $month
	 * @param DateTime $dateObject
	 * @return date range of present quarter
	 */
	public static function getNextQuarterRange($month = null, $dateObject = null)
	{
		$quarter = [];
		if (!$month) {
			$month = date('n');
		}
		if (!$dateObject) {
			$dateObject = new DateTime();
		}

		if ($month <= 3) { // 1st Quarter - January - March
			$quarter[0] = $dateObject->format('Y-04-01');
			$quarter[1] = $dateObject->format('Y-06-30');
		} else if ($month > 3 && $month <= 6) { // 2nd Quarter - April - June
			$quarter[0] = $dateObject->format('Y-07-01');
			$quarter[1] = $dateObject->format('Y-09-30');
		} else if ($month > 6 && $month <= 9) { // 3rd Quarter - July - September
			$quarter[0] = $dateObject->format('Y-10-01');
			$quarter[1] = $dateObject->format('Y-12-31');
		} else { // 4th Quarter - October - December
			$dateObject->modify('next year');
			$quarter[0] = $dateObject->format('Y-01-01');
			$quarter[1] = $dateObject->format('Y-03-31');
		}

		return $quarter;
	}
}
