<?php

/**
 * App config class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if (isset(self::$main[$key])) {
			return self::$main[$key];
		} elseif (isset($GLOBALS[$key])) {
			self::$main[$key] = $GLOBALS[$key];

			return $GLOBALS[$key];
		} else {
			require ROOT_DIRECTORY . '/config/config.php';
			if (isset($$key)) {
				self::$main[$key] = $$key;
				return $$key;
			}
			App\Log::warning("Parameter does not exist: $key");
			return $value;
		}
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
		switch ($argsLength) {
			case 2:
				if (!isset($moduleConfig[$key])) {
					return false;
				}

				return $moduleConfig[$key];
			default:
				return $moduleConfig;
		}
	}

	public static function api($key, $defvalue = false)
	{
		return self::$api[$key];
	}

	public static function debug($key, $defvalue = false)
	{
		if (empty(self::$debug)) {
			require_once 'config/debug.php';
			self::load('debug', $DEBUG_CONFIG);
		}

		return isset(self::$debug[$key]) ? self::$debug[$key] : false;
	}

	public static function developer($key, $defvalue = false)
	{
		if (empty(self::$developer)) {
			require_once 'config/developer.php';
			self::load('developer', $DEVELOPER_CONFIG);
		}

		return isset(self::$developer[$key]) ? self::$developer[$key] : false;
	}

	public static function security($key, $defvalue = false)
	{
		if (empty(self::$security)) {
			require_once 'config/security.php';
			self::load('security', $SECURITY_CONFIG);
		}

		return isset(self::$security[$key]) ? self::$security[$key] : false;
	}

	public static function securityKeys($key, $defvalue = false)
	{
		if (empty(self::$securityKeys)) {
			require_once 'config/secret_keys.php';
			self::load('securityKeys', $SECURITY_KEYS_CONFIG);
		}

		return isset(self::$securityKeys[$key]) ? self::$securityKeys[$key] : false;
	}

	public static function performance($key, $defvalue = false)
	{
		if (!self::$performance) {
			require_once 'config/performance.php';
			self::load('performance', $PERFORMANCE_CONFIG);
		}

		return isset(self::$performance[$key]) ? self::$performance[$key] : false;
	}

	public static function relation($key, $defvalue = false)
	{
		if (empty(self::$relation)) {
			require_once 'config/relation.php';
			self::load('relation', $RELATION_CONFIG);
		}

		return isset(self::$relation[$key]) ? self::$relation[$key] : false;
	}

	public static function sounds()
	{
		if (empty(self::$sounds)) {
			require_once 'config/sounds.php';
			self::load('sounds', $SOUNDS_CONFIG);
		}
		if (func_num_args() == 0) {
			return self::$sounds;
		}
		$key = func_get_args(1);

		return self::$sounds[$key];
	}

	public static function search($key, $defvalue = false)
	{
		if (empty(self::$search)) {
			require_once 'config/search.php';
			self::load('search', $CONFIG);
		}

		return self::$search[$key];
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
require_once ROOT_DIRECTORY . '/config/api.php';
require_once ROOT_DIRECTORY . '/config/config.php';
AppConfig::load('api', $API_CONFIG);
session_save_path(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'session');
if (!defined('IS_PUBLIC_DIR')) {
	define('IS_PUBLIC_DIR', false);
}
if (\AppConfig::debug('EXCEPTION_ERROR_HANDLER')) {
	\App\ErrorHandler::init();
}
