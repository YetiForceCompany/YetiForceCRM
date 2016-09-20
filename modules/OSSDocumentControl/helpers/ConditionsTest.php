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

class ConditionsTest
{

	public static function getValue($form, $name)
	{

		for ($i = 0; $i < count($form); $i++) {
			if ($form[$i]['name'] == $name) {
				return $form[$i]['value'];
			}
		}
	}

	public static function is($form, $cndArray)
	{

		$val = self::getValue($form, $cndArray['fieldname']);

		if ('date' == $cndArray['field_type']) {
			$format = vtlib\Functions::currentUserJSDateFormat($val);
			$format = str_replace('%', "", $format);
			$cndDate = DateTime::createFromFormat('Y-m-d', ($cndArray['val']));
			$recordDate = DateTime::createFromFormat($format, $val);

			if ($cndDate == $recordDate) {
				return TRUE;
			} else {
				return FALSE;
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


			if ($dateTime != FALSE) {
				if ($dateTime->diff($recordTime)->format('%R') == '+') {
					return true;
				} else {
					return false;
				}
			}
		} else {
			if ($cndArray['val'] == $val) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	public static function isNot($form, $cndArray)
	{

		$val = self::getValue($form, $cndArray['fieldname']);

		if ('date' == $cndArray['field_type']) {
			$format = vtlib\Functions::currentUserJSDateFormat($val);
			$format = str_replace('%', "", $format);
			$cndDate = DateTime::createFromFormat('Y-m-d', ($cndArray['val']));
			$recordDate = DateTime::createFromFormat($format, $val);

			if ($cndDate != $recordDate) {
				return TRUE;
			} else {
				return FALSE;
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

			if ($dateTime != FALSE) {
				if ($dateTime->diff($recordTime)->format('%R') != '+') {
					return true;
				} else {
					return false;
				}
			}
		} else {
			if ($cndArray['val'] != $val) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	public static function contains($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		if (strpos($val, $cndArray['val']) !== false) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function notContains($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		if (strpos($val, $cndArray['val']) === false) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function startsWith($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] === "" || strpos($val, $cndArray['val']) === 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function endsWith($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] === "" || substr($val, -strlen($cndArray['val'])) === $cndArray['val']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function isEmpty($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if (empty($val)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function isNotEmpty($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		if (!empty($val)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function isEnabled($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ('1' == $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function isDisabled($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ('0' == $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function equalTo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] == $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function lessThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] > $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function greaterThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] < $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function doesNotEqual($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] != $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function lessThanOrEqualTo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		if ($cndArray['val'] >= $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function greaterThanOrEqualTo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		if ($cndArray['val'] <= $val) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function after($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);
		$cndDate = DateTime::createFromFormat('Y-m-d', $cndArray['val']);
		$recordDate = DateTime::createFromFormat($format, $val);

		if ($cndDate < $recordDate) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function before($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);

		$cndDate = DateTime::createFromFormat('Y-m-d', $cndArray['val']); // data z warunku
		$recordDate = DateTime::createFromFormat($format, $val);

		if ($cndDate == $recordDate) {
			return FALSE;
		} else if ($cndDate > $recordDate) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function isToday($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		if ($recordDate == $cndDate) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// minej niz x dni temu
	public static function inLessThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = $cndArray['val'] * -1;

		if ($dayDiff > $maxInterval) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// wiecej niz x dni temu
	public static function inMoreThan($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = $cndArray['val'] * -1;

		if ($dayDiff < $maxInterval) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// x dni po dacie z pola
	public static function daysAgo($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = $cndArray['val'] * -1;

		if ($dayDiff == $maxInterval) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	// x dni po dacie z pola
	public static function daysLater($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);
		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);
		$recordDate = DateTime::createFromFormat($format, $val);
		$cndDate = new DateTime();

		$interval = $cndDate->diff($recordDate);
		$dayDiff = (int) $interval->format('%R%a');

		$maxInterval = (int) $cndArray['val'];

		if ($dayDiff == $maxInterval) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function between($form, $cndArray)
	{
		$val = self::getValue($form, $cndArray['fieldname']);

		$dates = explode(',', $cndArray['val']);
		list($startDate, $endDate) = $dates;
		$format = vtlib\Functions::currentUserJSDateFormat($val);
		$format = str_replace('%', "", $format);
		$startDate = DateTime::createFromFormat('Y-m-d', $startDate);
		$endDate = DateTime::createFromFormat('Y-m-d', $endDate);
		$testDate = DateTime::createFromFormat($format, $val);

		if ($testDate >= $startDate && $testDate <= $endDate) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
