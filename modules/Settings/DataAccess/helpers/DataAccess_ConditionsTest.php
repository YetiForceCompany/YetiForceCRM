<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class DataAccess_ConditionsTest
{

	public static function getValue($form, $name)
	{
		foreach ($form as $key => $row) {
			if ($key == $name) {
				return $row;
			}
		}
	}

	public static function is($form, $cndArray)
	{

		$val = self::getValue($form, $cndArray['fieldname']);

		if ('date' == $cndArray['field_type']) {
			$format = \App\Fields\DateTime::currentUserJSDateFormat();
			$cndDate = DateTime::createFromFormat('Y-m-d', ($cndArray['val']));
			$recordDate = DateTime::createFromFormat($format, $val);

			if ($cndDate == $recordDate) {
				return true;
			} else {
				return false;
			}
		} else if ('multipicklist' == $cndArray['field_type']) {

			$cndTab = explode('::', $cndArray['val']);
			$recordTab = explode(" |##| ", $val);

			sort($cndTab);
			sort($recordTab);

			return $cndTab == $recordTab;
		} else if ('time' == $cndArray['field_type']) {

			$dateTime = new DateTime($cndArray['val'] . ':00');
			$recordTime = new DateTime($val);


			if ($dateTime != false) {
				if ($dateTime->diff($recordTime)->format('%R') == '+') {
					return true;
				} else {
					return false;
				}
			}
		} else {
			if ($cndArray['val'] == $val) {
				return true;
			} else {
				return false;
			}
		}
	}

	public static function isNot($form, $cndArray)
	{

		$val = self::getValue($form, $cndArray['fieldname']);

		if ('date' == $cndArray['field_type']) {
			$format = \App\Fields\DateTime::currentUserJSDateFormat();
			$cndDate = DateTime::createFromFormat('Y-m-d', ($cndArray['val']));
			$recordDate = DateTime::createFromFormat($format, $val);

			if ($cndDate != $recordDate) {
				return true;
			} else {
				return false;
			}
		} else if ('multipicklist' == $cndArray['field_type']) {

			$cndTab = explode('::', $cndArray['val']);
			$recordTab = explode(" |##| ", $val);

			sort($cndTab);
			sort($recordTab);

			return $cndTab != $recordTab;
		} else if ('time' == $cndArray['field_type']) {

			$dateTime = new DateTime($cndArray['val'] . ':00');
			$recordTime = new DateTime($val);

			if ($dateTime != false) {
				if ($dateTime->diff($recordTime)->format('%R') != '+') {
					return true;
				} else {
					return false;
				}
			}
		} else {
			if ($cndArray['val'] != $val) {
				return true;
			} else {
				return false;
			}
		}
	}

	public static function contains($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if (strpos($val, $cndArray['val']) !== false) {
			return true;
		} else {
			return false;
		}
	}

	public static function notContains($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		if (strpos($val, $cndArray['val']) === false) {
			return true;
		} else {
			return false;
		}
	}

	public static function startsWith($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] === "" || strpos($val, $cndArray['val']) === 0) {
			return true;
		} else {
			return false;
		}
	}

	public static function endsWith($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] === "" || substr($val, -strlen($cndArray['val'])) === $cndArray['val']) {
			return true;
		} else {
			return false;
		}
	}

	public static function isEmpty($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if (empty($val)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isNotEmpty($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		if (!empty($val)) {
			return true;
		} else {
			return false;
		}
	}

	public static function isEnabled($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ('1' == $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function isDisabled($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ('0' == $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function equalTo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] == $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function lessThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] > $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function greaterThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] < $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function doesNotEqual($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] != $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function lessThanOrEqualTo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] >= $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function greaterThanOrEqualTo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		if ($cndArray['val'] <= $val) {
			return true;
		} else {
			return false;
		}
	}

	public static function after($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$cndDate = DateTime::createFromFormat('Y-m-d', $cndArray['val']);
		$recordDate = DateTime::createFromFormat($format, $val);

		if ($cndDate < $recordDate) {
			return true;
		} else {
			return false;
		}
	}

	public static function before($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$cndDate = DateTime::createFromFormat('Y-m-d', $cndArray['val']); // data z warunku
		$recordDate = DateTime::createFromFormat($format, $val);

		if ($cndDate == $recordDate) {
			return false;
		} else if ($cndDate > $recordDate) {
			return true;
		} else {
			return false;
		}
	}

	public static function isToday($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		if ($recordDate == $cndDate) {
			return true;
		} else {
			return false;
		}
	}

	// minej niz x dni temu
	public static function inLessThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = $cndArray['val'] * -1;

		if ($dayDiff > $maxInterval) {
			return true;
		} else {
			return false;
		}
	}

	// wiecej niz x dni temu
	public static function inMoreThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = $cndArray['val'] * -1;

		if ($dayDiff < $maxInterval) {
			return true;
		} else {
			return false;
		}
	}

	// x dni po dacie z pola
	public static function daysAgo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = $cndArray['val'] * -1;

		if ($dayDiff == $maxInterval) {
			return true;
		} else {
			return false;
		}
	}

	// x dni po dacie z pola
	public static function daysLater($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = (int) $cndArray['val'];

		if ($dayDiff == $maxInterval) {
			return true;
		} else {
			return false;
		}
	}

	public static function between($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		$dates = explode(',', $cndArray['val']);
		list($startDate, $endDate) = $dates;
		$format = \App\Fields\DateTime::currentUserJSDateFormat();
		$startDate = DateTime::createFromFormat('Y-m-d', $startDate);
		$endDate = DateTime::createFromFormat('Y-m-d', $endDate);
		$testDate = DateTime::createFromFormat($format, $val);

		if ($testDate >= $startDate && $testDate <= $endDate) {
			return true;
		} else {
			return false;
		}
	}

	public static function hasChanged($form, $cndArray)
	{
		$new_value = self::getValue($form, $cndArray['fieldname']);
		$pre_value = self::getValue($form, 'p_' . $cndArray['fieldname']);
		if (empty($new_value)) {
			return false;
		} else {
			return !($new_value == $pre_value);
		}
	}
}
