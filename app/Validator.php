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
	 * @param bool|int|string $input
	 *
	 * @return bool
	 */
	public static function bool($input): bool
	{
		return null !== filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
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
	 * @param int|string $input
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
	 * @param null|int $userId
	 *
	 * @return bool
	 */
	public static function dateInUserFormat(string $input, ?int $userId = null): bool
	{
		if (null === $userId) {
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
	 * @param null|int $userId
	 *
	 * @return bool
	 */
	public static function timeInUserFormat(string $input, ?int $userId = null): bool
	{
		if (null === $userId) {
			$userId = User::getCurrentUserId();
		}
		if ('12' === User::getUserModel($userId)->getDetail('hour_format')) {
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
	 * @param null|int $userId
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
			if ('12' === $userModel->getDetail('hour_format')) {
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
		return false !== filter_var($input, FILTER_VALIDATE_INT);
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
		return $input && explode('-', $input) === explode('_', \Locale::acceptFromHttp($input));
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
		return 0 === stripos($dbType, 'mysql');
	}

	/**
	 *  Function checks if given value is email.
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	public static function email(string $email): bool
	{
		return false !== filter_var($email, FILTER_VALIDATE_EMAIL) && $email === filter_var($email, FILTER_SANITIZE_EMAIL);
	}

	/**
	 * Function checks if given value is url.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function url(string $url): bool
	{
		return false !== filter_var($url, FILTER_VALIDATE_URL);
	}

	/**
	 * Function checks if given value is domain.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function domain(string $input): bool
	{
		return false !== filter_var($input, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
	}

	/**
	 * Function checks if given value is database type.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function dbType(string $input): bool
	{
		return isset((new Db())->schemaMap[$input]);
	}

	/**
	 * Function checks if given value is correct database name (mysql).
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function dbName(string $input): bool
	{
		return preg_match('/^[^\\/?%*:|\\\"<>.\s]{1,64}$/', $input);
	}

	/**
	 * Function checks if given value is port number.
	 *
	 * @param int $input
	 *
	 * @return bool
	 */
	public static function port($input): bool
	{
		return preg_match('/^([0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$/', $input);
	}

	/**
	 * Function checks if given value is valid SQl input.
	 *
	 * @param int|string $input
	 *
	 * @return bool
	 */
	public static function sql($input): bool
	{
		return preg_match('/^[_a-zA-Z0-9.,:]+$/', $input);
	}
}
