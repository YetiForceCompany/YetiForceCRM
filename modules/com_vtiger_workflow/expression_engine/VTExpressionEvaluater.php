<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */

class VTFieldExpressionEvaluater
{
	public static function __vt_add($arr)
	{
		if (1 == \count($arr)) {
			return $arr[0];
		}
		if (\strlen(substr(strrchr($arr[0], '.'), 1)) > \strlen(substr(strrchr($arr[1], '.'), 1))) {
			$maxDigit = \strlen(substr(strrchr($arr[0], '.'), 1));
		} else {
			$maxDigit = \strlen(substr(strrchr($arr[1], '.'), 1));
		}

		return bcadd($arr[0], $arr[1], $maxDigit);
	}

	public static function __vt_sub($arr)
	{
		if (1 == \count($arr)) {
			return -$arr[0];
		}
		if (\strlen(substr(strrchr($arr[0], '.'), 1)) > \strlen(substr(strrchr($arr[1], '.'), 1))) {
			$maxDigit = \strlen(substr(strrchr($arr[0], '.'), 1));
		} else {
			$maxDigit = \strlen(substr(strrchr($arr[1], '.'), 1));
		}

		return bcsub($arr[0], $arr[1], $maxDigit);
	}

	public static function __vt_mul($arr)
	{
		return (float) $arr[0] * (float) $arr[1];
	}

	public static function __vt_div($arr)
	{
		try {
			return $arr[0] / $arr[1];
		} catch (Exception $e) {
			return 0;
		}
	}

	public static function __vt_equals($arr)
	{
		return $arr[0] == $arr[1];
	}

	public static function __vt_ltequals($arr)
	{
		return $arr[0] <= $arr[1];
	}

	public static function __vt_gtequals($arr)
	{
		return $arr[0] >= $arr[1];
	}

	public static function __vt_lt($arr)
	{
		return $arr[0] < $arr[1];
	}

	public static function __vt_gt($arr)
	{
		return $arr[0] > $arr[1];
	}

	public static function __vt_concat($arr)
	{
		return implode('', $arr);
	}

	/** Date difference between (input times) or (current time and input time).
	 *
	 * @param array $a   $a[0] - Input time1, $a[1] - Input time2
	 *                   (if $a[1] is not available $a[0] = Current Time, $a[1] = Input time1)
	 * @param mixed $arr
	 *
	 * @return int difference timestamp
	 */
	public static function __vt_time_diff($arr)
	{
		$time_operand1 = $time_operand2 = 0;
		if (\count($arr) > 1) {
			$time_operand1 = $time1 = $arr[0];
			$time_operand2 = $time2 = $arr[1];
		} else {
			// Added as we need to compare with the values based on the user date format and timezone

			$time_operand1 = date('Y-m-d H:i:s'); // Current time

			$time_operand2 = $arr[0];
		}

		if (empty($time_operand1) || empty($time_operand2)) {
			return 0;
		}

		$time_operand1 = \App\Fields\DateTime::formatToDb($time_operand1, true);
		$time_operand2 = \App\Fields\DateTime::formatToDb($time_operand2, true);

		//to give the difference if it is only time field
		if (empty($time_operand1) && empty($time_operand2)) {
			$pattern = '/([01]?[0-9]|2[0-3]):[0-5][0-9]/';
			if (preg_match($pattern, $time1) && preg_match($pattern, $time2)) {
				$timeDiff = strtotime($time1) - strtotime($time2);

				return date('H:i:s', $timeDiff);
			}
		}
		return strtotime($time_operand1) - strtotime($time_operand2);
	}

	/**
	 * Calculate the time difference (input times) or (current time and input time) and
	 * convert it into number of days.
	 *
	 * @param array $a   $a[0] - Input time1, $a[1] - Input time2
	 *                   (if $a[1] is not available $a[0] = Current Time, $a[1] = Input time1)
	 * @param mixed $arr
	 *
	 * @return int number of days
	 */
	public static function __vt_time_diffdays($arr)
	{
		$timediff = static::__vt_time_diff($arr);
		return floor($timediff / (60 * 60 * 24));
	}

	public static function __vt_add_days($arr)
	{
		if (\count($arr) > 1) {
			$baseDate = $arr[0];
			$noOfDays = $arr[1];
		} else {
			$noOfDays = $arr[0];
		}
		if (null === $baseDate || empty($baseDate)) {
			$baseDate = date('Y-m-d'); // Current date
		}
		preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
		$baseDate = strtotime($match[0]);
		return strftime('%Y-%m-%d', $baseDate + ($noOfDays * 24 * 60 * 60));
	}

	public static function __vt_sub_days($arr)
	{
		if (\count($arr) > 1) {
			$baseDate = $arr[0];
			$noOfDays = $arr[1];
		} else {
			$noOfDays = $arr[0];
		}
		if (null === $baseDate || empty($baseDate)) {
			$baseDate = date('Y-m-d'); // Current date
		}
		preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
		$baseDate = strtotime($match[0]);
		return strftime('%Y-%m-%d', $baseDate - ($noOfDays * 24 * 60 * 60));
	}

	public static function __vt_get_date($arr)
	{
		$type = $arr[0];
		switch ($type) {
			case 'today':
				return date('Y-m-d');
			case 'tomorrow':
				return date('Y-m-d', strtotime('+1 day'));
			case 'yesterday':
				return date('Y-m-d', strtotime('-1 day'));
			default:
				return date('Y-m-d');
		}
	}

	public static function __vt_add_time($arr)
	{
		if (\count($arr) > 1) {
			$baseTime = $arr[0];
			$minutes = $arr[1];
		} else {
			$baseTime = date('H:i:s');
			$minutes = $arr[0];
		}
		$endTime = strtotime("+$minutes minutes", strtotime($baseTime));

		return date('H:i:s', $endTime);
	}

	public static function __vt_sub_time($arr)
	{
		if (\count($arr) > 1) {
			$baseTime = $arr[0];
			$minutes = $arr[1];
		} else {
			$baseTime = date('H:i:s');
			$minutes = $arr[0];
		}
		$endTime = strtotime("-$minutes minutes", strtotime($baseTime));

		return date('H:i:s', $endTime);
	}

	public function __construct($expr)
	{
		$this->operators = [
			'+' => '__vt_add',
			'-' => '__vt_sub',
			'*' => '__vt_mul',
			'/' => '__vt_div',
			'==' => '__vt_equals',
			'<=' => '__vt_ltequals',
			'>=' => '__vt_gtequals',
			'<' => '__vt_lt',
			'>' => '__vt_gt',
		];
		$this->functions = [
			'concat' => '__vt_concat',
			'time_diff' => '__vt_time_diff',
			'time_diffdays' => '__vt_time_diffdays',
			'add_days' => '__vt_add_days',
			'sub_days' => '__vt_sub_days',
			'get_date' => '__vt_get_date',
			'add_time' => '__vt_add_time',
			'sub_time' => '__vt_sub_time',
		];

		$this->operations = array_merge($this->functions, $this->operators);
		$this->expr = $expr;
	}

	public function evaluate($env)
	{
		$this->env = $env;

		return $this->exec($this->expr);
	}

	public function exec($expr)
	{
		if ($expr instanceof VTExpressionSymbol) {
			return $this->env($expr);
		}
		if ($expr instanceof VTExpressionTreeNode) {
			$op = $expr->getName();
			if ('if' == $op->value) {
				$params = $expr->getParams();
				$cond = $this->exec($params[0]);
				if ($cond) {
					return $this->exec($params[1]);
				}
				return $this->exec($params[2]);
			}
			$params = array_map([$this, 'exec'], $expr->getParams());
			$func = $this->operations[$op->value];

			return static::$func($params);
		}
		return $expr;
	}

	/**
	 * Gets an environment variable from available sources.
	 *
	 * @param VTExpressionSymbol $sym
	 *
	 * @return string
	 */
	public function env(VTExpressionSymbol $sym)
	{
		if ($this->env) {
			return $this->env->get($sym->value);
		}
		return $sym->value;
	}
}
