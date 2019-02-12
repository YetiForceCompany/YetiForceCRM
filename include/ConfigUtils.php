<?php

/**
 * App config class.
 *
 * @copyright  YetiForce Sp. z o.o
 * @license    YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 *
 * @deprecated Use \App\Config
 */
class AppConfig
{
	protected static $api = [];
	protected static $main = [];
	protected static $debug = [];
	protected static $developer = [];
	protected static $security = [];
	protected static $securityKeys = [];
	protected static $performance = [];
	protected static $relation = [];
	protected static $modules = [];
	protected static $sounds = [];
	protected static $search = [];

	/**
	 * Function to get main configuration of system.
	 *
	 * @deprecated Use \App\Config::main()
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function main($key, $value = false)
	{
		return \App\Config::main($key, $value);
	}

	/**
	 * @deprecated Use \App\Config::module()
	 */
	public static function module($module, $key = null, $defaultValue = null)
	{
		return \App\Config::module($module, $key, $defaultValue);
	}

	/**
	 * @deprecated Use \App\Config::api()
	 */
	public static function api($key, $defvalue = false)
	{
		return \App\Config::api($key, $defvalue);
	}

	/**
	 * @deprecated Use \App\Config::debug()
	 */
	public static function debug($key, $defvalue = false)
	{
		return \App\Config::debug($key, $defvalue);
	}

	/**
	 * @deprecated Use \App\Config::developer()
	 */
	public static function developer($key, $defvalue = false)
	{
		return \App\Config::developer($key, $defvalue);
	}

	/**
	 * @deprecated Use \App\Config::security()
	 */
	public static function security($key, $defvalue = false)
	{
		return \App\Config::security($key, $defvalue);
	}

	/**
	 * @deprecated Use \App\Config::securityKeys()
	 */
	public static function securityKeys($key, $defvalue = false)
	{
		return \App\Config::securityKeys($key, $defvalue);
	}

	/**
	 * @deprecated Use \App\Config::module()
	 */
	public static function performance($key, $defvalue = false)
	{
		return \App\Config::performance($key, $defvalue);
	}

	/**
	 * @deprecated Use \App\Config::relation()
	 */
	public static function relation($key, $defvalue = false)
	{
		return \App\Config::relation($key, $defvalue);
	}

	/**
	 * @deprecated Use \App\Config::sounds()
	 */
	public static function sounds(?string $arg = null, $default = null)
	{
		return \App\Config::sounds($arg, $default);
	}

	/**
	 * @deprecated Use \App\Config::search()
	 */
	public static function search($key, $defvalue = false)
	{
		return \App\Config::search($key, $defvalue);
	}

	public static function load($key, $config)
	{
		self::$$key = $config;
	}

	/**
	 * Set config value.
	 *
	 * @deprecated Use \App\Config::set()
	 *
	 * @return bool
	 */
	public static function set(): bool
	{
		return call_user_func_array('\App\Config::set', func_get_args());
	}
}

if (!defined('ROOT_DIRECTORY')) {
	define('ROOT_DIRECTORY', str_replace(DIRECTORY_SEPARATOR . 'include', '', __DIR__));
}
require_once ROOT_DIRECTORY . '/vendor/autoload.php';
session_save_path(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'session');
if (!defined('IS_PUBLIC_DIR')) {
	define('IS_PUBLIC_DIR', false);
}
if (\App\Config::debug('EXCEPTION_ERROR_HANDLER')) {
	\App\ErrorHandler::init();
}
if (($timeZone = \App\Config::main('default_timezone')) && function_exists('date_default_timezone_set')) {
	date_default_timezone_set($timeZone);
}
