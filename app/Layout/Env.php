<?php
/**
 * Layout environment file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Layout;

use App\Config;

/**
 * Layout environment class.
 */
class Env
{
	/**
	 * Get all environment.
	 *
	 * @param bool|string $mode
	 *
	 * @return array
	 */
	public static function getAll($mode = false): array
	{
		if (false === $mode) {
			$mode = \App\User::isLoggedIn() ? 'update' : 'loginPage';
		}
		return [
			'Env' => static::getEnv($mode),
			'Language' => static::getLanguage($mode),
			'Debug' => static::getDebug($mode),
			'Users' => static::getUser($mode),
		];
	}

	/**
	 * Get base environment.
	 *
	 * @param string $mode
	 *
	 * @return array
	 */
	public static function getEnv($mode = 'update'): array
	{
		if ('loginPage' === $mode) {
			return [
				'baseURL' => Config::main('site_URL'),
				'publicDir' => '/src',
				'routerMode' => 'hash',
				'dev' => 'test' === Config::main('systemMode')
			];
		}
		$env = [
			'siteUrl' => \App\Layout::getPublicUrl('', true),
			'layoutPath' => \App\Layout::getPublicUrl('layouts/' . \App\Layout::getActiveLayout()),
			'soundFilesPath' => \App\Layout::getPublicUrl('layouts/resources/sounds/'),
			'sounds' => Config::sounds(),
			'backgroundClosingModal' => Config::main('backgroundClosingModal'),
			'eventLimit' => Config::module('Calendar', 'EVENT_LIMIT'),
			'globalSearchAutocompleteActive' => Config::search('GLOBAL_SEARCH_AUTOCOMPLETE'),
			'globalSearchAutocompleteMinLength' => Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_MIN_LENGTH'),
			'globalSearchAutocompleteAmountResponse' => Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT'),
			'globalSearchDefaultOperator' => Config::search('GLOBAL_SEARCH_DEFAULT_OPERATOR'),
			'intervalForNotificationNumberCheck' => \Config\Performance::$INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK,
			'recordPopoverDelay' => \Config\Performance::$RECORD_POPOVER_DELAY,
			'searchShowOwnerOnlyInList' => \Config\Performance::$SEARCH_SHOW_OWNER_ONLY_IN_LIST,
			'fieldsReferencesDependent' => \Config\Security::$FIELDS_REFERENCES_DEPENDENT,
			'webSocketUrl' => \Config\WebSocket::$url,
		];
		if ('loginPage' !== $mode) {
			if (\App\Session::has('ShowAuthy2faModal')) {
				$env['showAuthy2faModal'] = \App\Session::get('ShowAuthy2faModal');
				if ('TOTP_OPTIONAL' === \Config\Security::$USER_AUTHY_MODE) {
					\App\Session::delete('ShowAuthy2faModal');
				}
			}
			if (\App\Session::has('ShowUserPasswordChange')) {
				$env['showUserPasswordChange'] = \App\Session::get('ShowUserPasswordChange');
				if (1 === (int) \App\Session::get('ShowUserPasswordChange')) {
					\App\Session::delete('ShowUserPasswordChange');
				}
			}
		}
		return $env;
	}

	/**
	 * Get language environment.
	 *
	 * @param string $mode
	 *
	 * @return array
	 */
	public static function getLanguage($mode = 'update'): array
	{
		if ('loginPage' === $mode) {
			$lang = \App\Language::getLanguage();
			return [
				'lang' => $lang,
				'translations' => \App\Language::getLanguageData($lang),
			];
		}
		return [
			'langPrefix' => \App\Language::getLanguage(),
			'langKey' => \App\Language::getShortLanguageName(),
		];
	}

	/**
	 * Get debug environment.
	 *
	 * @param string $mode
	 *
	 * @return array
	 */
	public static function getDebug($mode = 'update'): array
	{
		return [
			'isActive' => (bool) \Config\Debug::$JS_DEBUG,
			'levels' => \Config\Debug::$LOG_LEVELS
		];
	}

	/**
	 * Get user environment.
	 *
	 * @param string $mode
	 *
	 * @return array
	 */
	public static function getUser($mode = 'update'): array
	{
		if ('loginPage' === $mode) {
			$bruteForceInstance = \Settings\BruteForce\Models\Module::getCleanInstance();
			return [
				'isLoggedIn' => \App\User::isLoggedIn(),
				'isBlockedIp' => $bruteForceInstance->isActive() && $bruteForceInstance->isBlockedIp(),
				'loginPageRememberCredentials' => \Config\Security::$LOGIN_PAGE_REMEMBER_CREDENTIALS,
				'resetLoginPassword' => \Config\Security::$RESET_LOGIN_PASSWORD,
				'langInLoginView' => \App\Config::main('langInLoginView'),
				'layoutInLoginView' => \App\Config::main('layoutInLoginView'),
				'defaultLayout' => \App\Config::main('defaultLayout')
			];
		}
		$userModel = \App\User::getCurrentUserModel();
		if ($userModel->isActive()) {
			$details = $userModel->getDetails();
			return [
				'userId' => $userModel->getId(),
				'currencyId' => $details['currency_id'],
				'currencyName' => $details['currency_name'],
				'currencyCode' => $details['currency_code'],
				'currencySymbol' => $details['currency_symbol'],
				'currencyGroupingPattern' => $details['currency_grouping_pattern'],
				'currencyDecimalSeparator' => $details['currency_decimal_separator'],
				'currencyGroupingSeparator' => $details['currency_grouping_separator'],
				'currencySymbolPlacement' => $details['currency_symbol_placement'],
				'noOfCurrencyDecimals' => (int) $details['no_of_currency_decimals'],
				'truncateTrailingZeros' => $details['truncate_trailing_zeros'],
				'rowHeight' => $details['rowheight'],
				'dateFormat' => $details['date_format'],
				'dateFormatJs' => \App\Fields\Date::currentUserJSDateFormat($details['date_format']),
				'hourFormat' => $details['hour_format'],
				'startHour' => $details['start_hour'],
				'endHour' => $details['end_hour'],
				'firstDayOfWeek' => $details['dayoftheweek'],
				'firstDayOfWeekNo' => \App\Fields\Date::$dayOfWeek[$details['dayoftheweek']] ?? false,
				'timeZone' => $details['time_zone'],
			];
		}
		return [];
	}
}
