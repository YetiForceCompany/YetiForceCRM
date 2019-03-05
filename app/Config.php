<?php
/**
 * Config main class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Config main class.
 */
class Config
{
	/**
	 * Js environment variables.
	 *
	 * @var array
	 */
	private static $jsEnv = [];

	/**
	 * Get all js configuration in json.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public static function getJsEnv()
	{
		if (empty(self::$jsEnv)) {
			self::loadJsConfig();
		}
		return self::$jsEnv;
	}

	/**
	 * Load js config.
	 */
	public static function loadJsConfig()
	{
		$userModel = User::getCurrentUserModel();
		foreach ([
					 'siteUrl' => Layout::getPublicUrl('', true),
					 'layoutPath' => Layout::getPublicUrl('layouts/' . Layout::getActiveLayout()),
					 'langPrefix' => Language::getLanguage(),
					 'langKey' => Language::getShortLanguageName(),
					 'dateFormat' => $userModel->getDetail('date_format'),
					 'dateFormatJs' => Fields\Date::currentUserJSDateFormat($userModel->getDetail('date_format')),
					 'hourFormat' => $userModel->getDetail('hour_format'),
					 'startHour' => $userModel->getDetail('start_hour'),
					 'endHour' => $userModel->getDetail('end_hour'),
					 'firstDayOfWeek' => $userModel->getDetail('dayoftheweek'),
					 'firstDayOfWeekNo' => Fields\Date::$dayOfWeek[$userModel->getDetail('dayoftheweek')] ?? false,
					 'eventLimit' => self::module('Calendar', 'EVENT_LIMIT'),
					 'timeZone' => $userModel->getDetail('time_zone'),
					 'currencyId' => $userModel->getDetail('currency_id'),
					 'currencyName' => $userModel->getDetail('currency_name'),
					 'currencyCode' => $userModel->getDetail('currency_code'),
					 'currencySymbol' => $userModel->getDetail('currency_symbol'),
					 'currencyGroupingPattern' => $userModel->getDetail('currency_grouping_pattern'),
					 'currencyDecimalSeparator' => $userModel->getDetail('currency_decimal_separator'),
					 'currencyGroupingSeparator' => $userModel->getDetail('currency_grouping_separator'),
					 'currencySymbolPlacement' => $userModel->getDetail('currency_symbol_placement'),
					 'noOfCurrencyDecimals' => (int) $userModel->getDetail('no_of_currency_decimals'),
					 'truncateTrailingZeros' => $userModel->getDetail('truncate_trailing_zeros'),
					 'rowHeight' => $userModel->getDetail('rowheight'),
					 'userId' => $userModel->getId(),
					 'backgroundClosingModal' => self::main('backgroundClosingModal'),
					 'globalSearchAutocompleteActive' => self::search('GLOBAL_SEARCH_AUTOCOMPLETE'),
					 'globalSearchAutocompleteMinLength' => self::search('GLOBAL_SEARCH_AUTOCOMPLETE_MIN_LENGTH'),
					 'globalSearchAutocompleteAmountResponse' => self::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT'),
					 'globalSearchDefaultOperator' => self::search('GLOBAL_SEARCH_DEFAULT_OPERATOR'),
					 'sounds' => self::sounds(),
					 'intervalForNotificationNumberCheck' => self::performance('INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK'),
					 'recordPopoverDelay' => self::performance('RECORD_POPOVER_DELAY'),
					 'searchShowOwnerOnlyInList' => self::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST'),
					 'fieldsReferencesDependent' => self::security('FIELDS_REFERENCES_DEPENDENT'),
					 'soundFilesPath' => Layout::getPublicUrl('layouts/resources/sounds/'),
					 'debug' => (bool) self::debug('JS_DEBUG'),
				 ] as $key => $value) {
			self::setJsEnv($key, $value);
		}
		if (Session::has('ShowAuthy2faModal')) {
			self::setJsEnv('ShowAuthy2faModal', Session::get('ShowAuthy2faModal'));
			if (self::security('USER_AUTHY_MODE') === 'TOTP_OPTIONAL') {
				Session::delete('ShowAuthy2faModal');
			}
		}
		if (Session::has('ShowUserPasswordChange')) {
			self::setJsEnv('ShowUserPasswordChange', Session::get('ShowUserPasswordChange'));
			if ((int) Session::get('ShowUserPasswordChange') === 1) {
				Session::delete('ShowUserPasswordChange');
			}
		}
	}

	/**
	 * Set js environment variables.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setJsEnv($key, $value)
	{
		self::$jsEnv[$key] = $value;
	}

	/**
	 * Gets main configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function main(?string $arg = null, $default = null)
	{
		if ($arg && isset($GLOBALS[$arg])) {
			return $GLOBALS[$arg];
		}
		$class = "\Config\Main";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets module configuration.
	 *
	 * @param string      $moduleName
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function module(string $moduleName, ?string $arg = null, $default = null)
	{
		$class = "\Config\Modules\\$moduleName";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets component configuration.
	 *
	 * @param string      $component
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function component(string $component, ?string $arg = null, $default = null)
	{
		$class = "\Config\Components\\$component";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets performance configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function performance(?string $arg = null, $default = null)
	{
		$class = "\Config\Performance";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets api configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function api(?string $arg = null, $default = null)
	{
		$class = "\Config\Api";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets debug configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function debug(?string $arg = null, $default = null)
	{
		$class = "\Config\Debug";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets developer configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function developer(?string $arg = null, $default = null)
	{
		$class = "\Config\Developer";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets security configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function security(?string $arg = null, $default = null)
	{
		$class = "\Config\Security";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets search configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function search(?string $arg = null, $default = null)
	{
		$class = "\Config\Search";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets sounds configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function sounds(?string $arg = null, $default = null)
	{
		$class = "\Config\Sounds";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets relation configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function relation(?string $arg = null, $default = null)
	{
		$class = "\Config\Relation";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets security keys configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function securityKeys(?string $arg = null, $default = null)
	{
		$class = "\Config\SecurityKeys";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets database configuration.
	 *
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function db(?string $arg = null, $default = null)
	{
		$class = "\Config\Db";
		return self::get($class, $arg, $default);
	}

	/**
	 * Gets configuration for class.
	 *
	 * @param string      $class
	 * @param string|null $arg
	 * @param mixed       $default
	 *
	 * @throws \ReflectionException
	 *
	 * @return mixed
	 */
	public static function get(string $class, ?string $arg = null, $default = null)
	{
		$value = $default;
		if (\class_exists($class)) {
			if ($arg === null) {
				$object = (new \ReflectionClass($class));
				$value = $object->getStaticProperties();
				foreach ($object->getMethods() as $method) {
					$value[$method->getName()] = \call_user_func("{$class}::{$method->getName()}");
				}
			} elseif (isset($class::$$arg)) {
				$value = $class::$$arg;
			} elseif (\method_exists($class, $arg)) {
				$value = \call_user_func("{$class}::{$arg}");
			}
		}
		return $value;
	}

	/**
	 * Set config value.
	 *
	 * @return bool
	 */
	public static function set(): bool
	{
		if (4 === func_num_args()) {
			[$component, $type, $key, $value] = func_get_args();
		} else {
			[$type, $key, $value] = func_get_args();
		}
		$class = '\Config\\' . (isset($component) ? ucfirst($component) . 's\\' : '') . ucfirst($type);
		if ($result = (class_exists($class) && isset($class::$$key))) {
			$class::$$key = $value;
		}
		return $result;
	}
}
