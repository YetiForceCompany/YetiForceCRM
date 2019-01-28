<?php

/**
 * App config class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function main($key, $value = false)
	{
		return \App\Config::main($key, $value);
	}

	public static function module($module, $key = null, $defaultValue = null)
	{
		return \App\Config::module($module, $key, $defaultValue);
	}

	public static function api($key, $defvalue = false)
	{
		return \App\Config::api($key, $defvalue);
	}

	public static function debug($key, $defvalue = false)
	{
		return \App\Config::debug($key, $defvalue);
	}

	public static function developer($key, $defvalue = false)
	{
		return \App\Config::developer($key, $defvalue);
	}

	public static function security($key, $defvalue = false)
	{
		return \App\Config::security($key, $defvalue);
	}

	public static function securityKeys($key, $defvalue = false)
	{
		return \App\Config::securityKeys($key, $defvalue);
	}

	public static function performance($key, $defvalue = false)
	{
		return \App\Config::performance($key, $defvalue);
	}

	public static function relation($key, $defvalue = false)
	{
		return \App\Config::relation($key, $defvalue);
	}

	public static function sounds(?string $arg = null, $default = null)
	{
		return \App\Config::search($arg, $default);
	}

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
	 * @param string $config
	 * @param string $key
	 * @param miexd  $value
	 */
	public static function set($config, $key, $value)
	{
		self::$$config[$key] = $value;
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
if (\AppConfig::debug('EXCEPTION_ERROR_HANDLER')) {
	\App\ErrorHandler::init();
}
