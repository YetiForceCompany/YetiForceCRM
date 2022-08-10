<?php
/**
 * Validator basic class.
 *
 * @package   App
 *
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @copyright YetiForce S.A.
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
	 * Function verifies if given value contains only words, digits  or space.
	 *
	 * @param int|string $input
	 *
	 * @return bool
	 */
	public static function alnumSpace($input): bool
	{
		return preg_match('/^[[:alnum:]_ ]+$/', $input);
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
		return Fields\Date::isValid($input);
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
		if (null === $userId) {
			$userId = User::getCurrentUserId();
		}
		return Fields\Date::isValid($input, User::getUserModel($userId)->getDetail('date_format'));
	}

	/**
	 * Function verifies if given value is compatible with date time in ISO format.
	 *
	 * @param string   $input
	 * @param int|null $userId
	 *
	 * @return bool
	 */
	public static function dateTimeInIsoFormat(string $input): bool
	{
		return preg_match('/^(-?(?:[1-9][0-9]*)?[0-9]{4})-(1[0-2]|0[1-9])-(3[01]|0[1-9]|[12][0-9])T(2[0-3]|[01][0-9]):([0-5][0-9]):([0-5][0-9])(.[0-9]+)?(Z)?$/', $input);
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
		if (($arrInput = \explode(' ', $input)) && 2 === \count($arrInput)) {
			[$dateInput, $timeInput] = $arrInput;
			[$y, $m, $d] = Fields\Date::explode($dateInput);
			$result = checkdate($m, $d, $y) && is_numeric($y) && is_numeric($m) && is_numeric($d)
				&& preg_match('/(2[0-3]|[0][0-9]|1[0-9]):([0-5][0-9]):([0-5][0-9])/', $timeInput);
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
		if (($arrInput = \explode(' ', $input, 2)) && 2 === \count($arrInput)) {
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
	 * Function verifies if given value is float type.
	 *
	 * @param float|string $input
	 *
	 * @return bool
	 */
	public static function float($input): bool
	{
		return false !== filter_var($input, FILTER_VALIDATE_FLOAT);
	}

	/**
	 * Check if floating point numbers are equal.
	 *
	 * @see https://www.php.net/manual/en/language.types.float.php
	 *
	 * @param float $value1
	 * @param float $value2
	 * @param int   $precision
	 * @param mixed $rounding
	 *
	 * @return bool
	 */
	public static function floatIsEqual(float $value1, float $value2, int $precision = 2, $rounding = true): bool
	{
		if ($rounding) {
			$value1 = round($value1, $precision);
			$value2 = round($value2, $precision);
		}
		return 0 === bccomp($value1, $value2, $precision);
	}

	/**
	 * Check if floating point numbers are equal. Get the precision of the number from the user's settings.
	 *
	 * @param float $value1
	 * @param float $value2
	 *
	 * @return bool
	 */
	public static function floatIsEqualUserCurrencyDecimals(float $value1, float $value2): bool
	{
		return static::floatIsEqual($value1, $value2, (int) \App\User::getCurrentUserModel()->getDetail('no_of_currency_decimals'));
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
		return false !== filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) && $email === filter_var($email, FILTER_SANITIZE_EMAIL);
	}

	/**
	 *  Function checks if given value is email.
	 *
	 * @param string|array $emails
	 *
	 * @return bool
	 */
	public static function emails($emails): bool
	{
		$emails = \is_string($emails) ? explode(',', $emails) : $emails;
		foreach ($emails as $email) {
			if ($email && !self::email($email)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Function checks if given value is url or domain.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function urlDomain(string $url): bool
	{
		if (false === strpos($url, '://')) {
			return static::domain($url);
		}
		return static::url($url);
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
		if (!parse_url($url, PHP_URL_SCHEME)) {
			$url = 'http://' . $url;
		}
		if (mb_strlen($url) != \strlen($url) && \function_exists('idn_to_ascii') && \defined('INTL_IDNA_VARIANT_UTS46')) {
			$url = preg_replace_callback('/:\/\/([^\/]+)/', fn ($matches) => '://' . idn_to_ascii($matches[1], IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46), $url);
		}
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
		if (mb_strlen($input) != \strlen($input) && \function_exists('idn_to_ascii') && \defined('INTL_IDNA_VARIANT_UTS46')) {
			$input = idn_to_ascii($input, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
		}
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
	 * Function checks if given value is valid db user name.
	 *
	 * @param int|string $input
	 *
	 * @return bool
	 */
	public static function dbUserName($input): bool
	{
		return preg_match('/^[_a-zA-Z0-9.,:-]+$/', $input);
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

	/**
	 * Check if input is an time period value.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function timePeriod($input): bool
	{
		return preg_match('/^[0-9]{1,18}\:(d|H|i){1}$/', $input);
	}

	/**
	 * Check if input is an ip value.
	 *
	 * @param string|string[] $input
	 *
	 * @return bool
	 */
	public static function ip($input): bool
	{
		$input = \is_array($input) ? $input : [$input];
		$result = true;
		foreach ($input as $ipAddress) {
			if (false === filter_var($ipAddress, FILTER_VALIDATE_IP)) {
				$result = false;
				break;
			}
		}
		return $result;
	}

	/**
	 * Check if input is an ip or domain value.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function ipOrDomain($input): bool
	{
		return self::ip($input) || self::domain($input);
	}

	/**
	 * Function verifies if given value is text.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function text(string $input): bool
	{
		return Purifier::decodeHtml(Purifier::purify($input)) === $input;
	}

	/**
	 * Check directory name.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function dirName(string $input): bool
	{
		return !preg_match('/[\\/:\*\?"<>|]/', $input) && false === strpos($input, '.', -1);
	}

	/**
	 * Check path.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function path(string $input): bool
	{
		$parts = explode('/', trim(str_replace(\DIRECTORY_SEPARATOR, '/', $input), '/'));
		return !array_filter($parts, fn ($dir) => !self::dirName($dir));
	}

	/**
	 * Check base64.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function base64(string $input): bool
	{
		if (empty($input)) {
			return false;
		}
		$explode = explode(',', $input);
		return 2 === \count($explode) && 1 === preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[1]);
	}

	/**
	 * Check font icon name.
	 *
	 * @param string $input
	 *
	 * @return bool
	 */
	public static function fontIcon(string $input): bool
	{
		return !empty($input) && preg_match('/^[[:alnum:]_\- ]+$/', $input);
	}
}
