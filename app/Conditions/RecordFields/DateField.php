<?php

/**
 * Date field condition record field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

use App\Log;

/**
 * Date field condition record field class.
 */
class DateField extends BaseField
{
	use \App\Conditions\RecordTraits\Comparison;
	use \App\Conditions\RecordTraits\ComparisonField;

	/** {@inheritdoc} */
	public function check()
	{
		$fn = 'operator' . ucfirst($this->operator);
		if (isset(\App\Condition::DATE_OPERATORS[$this->operator]) && !method_exists($this, $fn)) {
			$fn = 'getStdOperator';
		}
		if (method_exists($this, $fn)) {
			Log::trace("Entering to $fn in " . __CLASS__);
			return $this->{$fn}();
		}
		Log::error("Not found operator: $fn in  " . __CLASS__);
		return false;
	}

	/**
	 * Custom operator.
	 *
	 * @return bool
	 */
	public function operatorCustom()
	{
		[$startDate, $endDate] = explode(',', $this->value);
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d', strtotime($startDate))) && ($dateValue <= date('Y-m-d', strtotime($endDate)));
	}

	/**
	 * Today operator.
	 *
	 * @return bool
	 */
	public function operatorToday()
	{
		return date('Y-m-d', strtotime($this->getValue())) === date('Y-m-d');
	}

	/**
	 * Smaller operator.
	 *
	 * @return bool
	 */
	public function operatorSmaller()
	{
		return date('Y-m-d', strtotime($this->getValue())) < date('Y-m-d');
	}

	/**
	 * Greater operator.
	 *
	 * @return bool
	 */
	public function operatorGreater()
	{
		return date('Y-m-d', strtotime($this->getValue())) > date('Y-m-d');
	}

	/**
	 * Greater operator.
	 *
	 * @return bool
	 */
	public function operatorPrevfy()
	{
		return date('Y', strtotime($this->getValue())) === date('Y', strtotime('last year'));
	}

	/**
	 * Thisfy operator.
	 *
	 * @return bool
	 */
	public function operatorThisfy()
	{
		return date('Y', strtotime($this->getValue())) === date('Y');
	}

	/**
	 * Nextfy operator.
	 *
	 * @return bool
	 */
	public function operatorNextfy()
	{
		return date('Y', strtotime($this->getValue())) === date('Y', strtotime('next year'));
	}

	/**
	 * Prevfq operator.
	 *
	 * @return bool
	 */
	public function operatorPrevfq()
	{
		return (ceil(date('n', strtotime($this->value)) / 3)) === (ceil(date('n') / 3) - 1);
	}

	/**
	 * Thisfq operator.
	 *
	 * @return bool
	 */
	public function operatorThisfq()
	{
		return (ceil(date('n', strtotime($this->value)) / 3)) === (ceil(date('n') / 3));
	}

	/**
	 * Nextfq operator.
	 *
	 * @return bool
	 */
	public function operatorNextfq()
	{
		return (ceil(date('n', strtotime($this->value)) / 3)) === (ceil(date('n') / 3) + 1);
	}

	/**
	 * Yesterday operator.
	 *
	 * @return bool
	 */
	public function operatorYesterday()
	{
		return date('Y-m-d', strtotime($this->getValue())) === date('Y-m-d', strtotime('last day'));
	}

	/**
	 * Untiltoday operator.
	 *
	 * @return bool
	 */
	public function operatorUntiltoday()
	{
		return date('Y-m-d', strtotime($this->getValue())) <= date('Y-m-d');
	}

	/**
	 * Tomorrow operator.
	 *
	 * @return bool
	 */
	public function operatorTomorrow()
	{
		return date('Y-m-d', strtotime($this->getValue())) === date('Y-m-d', strtotime('tomorrow'));
	}

	/**
	 * Lastweek operator.
	 *
	 * @return bool
	 */
	public function operatorLastweek()
	{
		$startDay = date('Y-m-d', strtotime('last week'));
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $startDay) && ($dateValue <= date('Y-m-d', strtotime($startDay . '+6 day')));
	}

	/**
	 * Thisweek operator.
	 *
	 * @return bool
	 */
	public function operatorThisweek()
	{
		$startDay = date('Y-m-d', strtotime('this week'));
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $startDay) && ($dateValue <= date('Y-m-d', strtotime($startDay . '+6 day')));
	}

	/**
	 * Nextweek operator.
	 *
	 * @return bool
	 */
	public function operatorNextweek()
	{
		$startDay = date('Y-m-d', strtotime('next week'));
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $startDay) && ($dateValue <= date('Y-m-d', strtotime($startDay . '+6 day')));
	}

	/**
	 * Lastmonth operator.
	 *
	 * @return bool
	 */
	public function operatorLastmonth()
	{
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-01', strtotime('first day of last month'))) && ($dateValue <= date('Y-m-t', strtotime('first day of last month')));
	}

	/**
	 * Thismonth operator.
	 *
	 * @return bool
	 */
	public function operatorThismonth()
	{
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-01', strtotime('this month'))) && ($dateValue <= date('Y-m-t', strtotime('this month')));
	}

	/**
	 * Nextmonth operator.
	 *
	 * @return bool
	 */
	public function operatorNextmonth()
	{
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-01', strtotime('first day of next month'))) && ($dateValue <= date('Y-m-t', strtotime('first day of next month')));
	}

	/**
	 * Last7days operator.
	 *
	 * @return bool
	 */
	public function operatorLast7days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d', strtotime($today . '-6 day'))) && ($dateValue <= $today);
	}

	/**
	 * Last15days operator.
	 *
	 * @return bool
	 */
	public function operatorLast15days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d', strtotime($today . '-14 day'))) && ($dateValue <= $today);
	}

	/**
	 * Last30days operator.
	 *
	 * @return bool
	 */
	public function operatorLast30days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d', strtotime($today . '-29 day'))) && ($dateValue <= $today);
	}

	/**
	 * Last60days operator.
	 *
	 * @return bool
	 */
	public function operatorLast60days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d', strtotime($today . '-59 day'))) && ($dateValue <= $today);
	}

	/**
	 * Last90days operator.
	 *
	 * @return bool
	 */
	public function operatorLast90days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d', strtotime($today . '-89 day'))) && ($dateValue <= $today);
	}

	/**
	 * Last120days operator.
	 *
	 * @return bool
	 */
	public function operatorLast120days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d', strtotime($today . '-119 day'))) && ($dateValue <= $today);
	}

	/**
	 * Next15days operator.
	 *
	 * @return bool
	 */
	public function operatorNext15days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $today) && ($dateValue <= date('Y-m-d', strtotime($today . '+14 day')));
	}

	/**
	 * Next30days operator.
	 *
	 * @return bool
	 */
	public function operatorNext30days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $today) && ($dateValue <= date('Y-m-d', strtotime($today . '+29 day')));
	}

	/**
	 * Next60days operator.
	 *
	 * @return bool
	 */
	public function operatorNext60days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $today) && ($dateValue <= date('Y-m-d', strtotime($today . '+59 day')));
	}

	/**
	 * Next90days operator.
	 *
	 * @return bool
	 */
	public function operatorNext90days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $today) && ($dateValue <= date('Y-m-d', strtotime($today . '+89 day')));
	}

	/**
	 * Next120days operator.
	 *
	 * @return bool
	 */
	public function operatorNext120days()
	{
		$today = date('Y-m-d');
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		return ($dateValue >= $today) && ($dateValue <= date('Y-m-d', strtotime($today . '+119 day')));
	}

	/**
	 * MoreThanDaysAgo operator.
	 *
	 * @return bool
	 */
	public function operatorMoreThanDaysAgo()
	{
		return $this->getValue() <= date('Y-m-d', strtotime('-' . $this->value . ' days'));
	}

	/**
	 * Between operator.
	 *
	 * @return array
	 */
	public function operatorBw()
	{
		return $this->operatorCustom();
	}

	/**
	 * Greater operator.
	 *
	 * @return array
	 */
	public function operatorGreaterthannow()
	{
		return $this->operatorGreater();
	}

	/**
	 * Smaller operator.
	 *
	 * @return array
	 */
	public function operatorSmallerthannow()
	{
		return $this->operatorSmaller();
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getStdOperator()
	{
		$dateValue = date('Y-m-d', strtotime($this->getValue()));
		$value = \DateTimeRange::getDateRangeByType($this->operator);
		if ($value[0] === $value[1]) {
			return $dateValue <= $value[0];
		}
		return ($dateValue >= $value[0]) && ($dateValue <= $value[1]);
	}
}
