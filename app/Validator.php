<?php
/**
 * Validator basic class.
 *
 * @package   App
 *
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @copyright YetiForce Sp. z o.o
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class Validator.
 */
class Validator
{
	/**
	 * Function verifies if given value can be recognized as bool.
	 *
	 * @param string|bool|int $input
	 *
	 * @return bool
	 */
	public static function bool($input): bool
	{
		return filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
	}

	/**
	 * Function verifies if given value is standard text.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function standard(string $input): bool
	{
		return preg_match('/^[\-_a-zA-Z]+$/', $input);
	}

	/**
	 * Function verifies if given value contains only words or digits.
	 *
	 * @param string|int $input
	 *
	 * @return bool
	 */
	public static function alnum($input): bool
	{
		return preg_match('/^[[:alnum:]_]+$/', $input);
	}

	/**
	 * Function verifies if given value is compatible with default data format.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function date(string $input): bool
	{
		[$y, $m, $d] = Fields\Date::explode($input);
		return checkdate($m, $d, $y) && is_numeric($y) && is_numeric($m) && is_numeric($d);
	}

	/**
	 * Function verifies if given value is compatible with user’s date format.
	 *
	 * @param string   $input
	 * @param int|null $userId
	 *
	 * @return bool
	 */
	public static function dateInUserFormat(string $input, ?int $userId = null): bool
	{
		if ($userId === null) {
			$userId = User::getCurrentUserId();
		}
		[$y, $m, $d] = Fields\Date::explode($input, User::getUserModel($userId)->getDetail('date_format'));
		return checkdate($m, $d, $y) && is_numeric($y) && is_numeric($m) && is_numeric($d);
	}

	/**
	 * Function verifies if given value is compatible with default time format.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function time(string $input): bool
	{
		return preg_match('/^(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9]):([0-5][0-9])$/', $input);
	}

	/**
	 *  Function verifies if given value is compatible with user’s time format.
	 *
	 * @param string   $input
	 * @param int|null $userId
	 *
	 * @return bool
	 */
	public static function timeInUserFormat(string $input, ?int $userId = null): bool
	{
		if (null === $userId) {
			$userId = User::getCurrentUserId();
		}
		if (User::getUserModel($userId)->getDetail('hour_format') === '12') {
			$pattern = '/^([0][0-9]|1[0-2]):([0-5][0-9])([ ]PM|[ ]AM|PM|AM)$/';
		} else {
			$pattern = '/^(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9])$/';
		}
		return preg_match($pattern, $input);
	}

	/**
	 * Function verifies if given value is compatible with default date and time format.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function dateTime(string $input): bool
	{
		$result = false;
		if (($arrInput = \explode(' ', $input)) && 2 === count($arrInput)) {
			[$dateInput, $timeInput] = $arrInput;
			[$y, $m, $d] = Fields\Date::explode($dateInput);
			$result = checkdate($m, $d, $y) && is_numeric($y) && is_numeric($m) && is_numeric($d) &&
				preg_match('/(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9]):([0-5][0-9])/', $timeInput);
		}
		return $result;
	}

	/**
	 * Function verifies if given value is compatible with user’s  date and time format.
	 *
	 * @param string   $input
	 * @param int|null $userId
	 *
	 * @return bool
	 */
	public static function dateTimeInUserFormat(string $input, ?int $userId = null): bool
	{
		$result = false;
		if (($arrInput = \explode(' ', $input)) && 2 === count($arrInput)) {
			$userModel = User::getUserModel($userId ?? User::getCurrentUserId());
			[$dateInput, $timeInput] = $arrInput;
			[$y, $m, $d] = Fields\Date::explode($dateInput, $userModel->getDetail('date_format'));
			if ($userModel->getDetail('hour_format') === '12') {
				$pattern = '/^(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9])(:([0-5][0-9]))?([ ]PM|[ ]AM|PM|AM)?$/';
			} else {
				$pattern = '/^(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9])(:([0-5][0-9]))?$/';
			}
			$result = checkdate($m, $d, $y) && is_numeric($y) && is_numeric($m) && is_numeric($d) && preg_match($pattern, $timeInput);
		}
		return $result;
	}

	/**
	 * Function verifies if given value is integer type.
	 *
	 * @param int|string $input
	 *
	 * @return bool
	 */
	public static function integer($input): bool
	{
		return filter_var($input, FILTER_VALIDATE_INT) !== false;
	}

	/**
	 * Function verifies if given value is a natural number.
	 *
	 * @param int|string $input
	 *
	 * @return bool
	 */
	public static function naturalNumber($input): bool
	{
		return preg_match('/^[0-9]+$/', $input);
	}

	/**
	 * Function verifies if given value is a correct language tag.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function languageTag(string $input): bool
	{
		return $input && explode('-', $input) === explode('_', Locale::acceptFromHttp($input));
	}

	/**
	 * Function checks if its mysql type.
	 *
	 * @param string $dbType
	 *
	 * @return bool
	 */
	public static function isMySQL(string $dbType): bool
	{
		return stripos($dbType, 'mysql') === 0;
	}
}
