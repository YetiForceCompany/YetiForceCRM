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

	public static function module()
	{
		$argsLength = func_num_args();
		$args = func_get_args();
		$module = $args[0];
		if ($argsLength === 2) {
			$key = $args[1];
		}
		if (isset(self::$modules[$module])) {
			switch ($argsLength) {
				case 1:
					return self::$modules[$module];
				case 2:
					if (isset(self::$modules[$module][$key])) {
						return self::$modules[$module][$key];
					}
					App\Log::warning("Parameter does not exist: $module, $key");

					return null;
				default:
					break;
			}
		}
		$fileName = "config/modules/$module.php";
		if (!file_exists($fileName)) {
			return false;
		}
		$moduleConfig = require $fileName;
		if (empty($moduleConfig)) {
			return false;
		}
		self::$modules[$module] = $moduleConfig;
		if ($argsLength === 2) {
			if (!isset($moduleConfig[$key])) {
				return false;
			}
			return $moduleConfig[$key];
		} else {
			return $moduleConfig;
		}
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
		if (empty(self::$relation)) {
			require_once 'config/relation.php';
			self::load('relation', $RELATION_CONFIG);
		}
		return self::$relation[$key] ?? $defvalue;
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
