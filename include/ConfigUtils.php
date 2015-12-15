<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class AppConfig
{

	protected static $main = [];
	protected static $calendar = [];
	protected static $debug = [];
	protected static $developer = [];
	protected static $security = [];
	protected static $securityKeys = [];
	protected static $performance = [];

	public static function load($key, $config)
	{
		switch ($key) {
			case 'calendar':
				self::$calendar = $config;
				break;
			case 'debug':
				self::$debug = $config;
				break;
			case 'developer':
				self::$developer = $config;
				break;
			case 'security':
				self::$security = $config;
				break;
			case 'securityKeys':
				self::$securityKeys = $config;
				break;
			case 'performance':
				self::$performance = $config;
				break;
		}
	}

	public static function main($key, $value = false)
	{
		if (key_exists($key, $GLOBALS)) {
			self::$main[$key] = $GLOBALS[$key];
			return $GLOBALS[$key];
		} elseif (key_exists($key, self::$main)) {
			return self::$main[$key];
		}
		return $value;
	}

	public static function calendar($key, $defvalue = false)
	{
		return self::$calendar[$key];
	}

	public static function debug($key, $defvalue = false)
	{
		return self::$debug[$key];
	}

	public static function developer($key, $defvalue = false)
	{
		return self::$developer[$key];
	}

	public static function security($key, $defvalue = false)
	{
		return self::$security[$key];
	}

	public static function securityKeys($key, $defvalue = false)
	{
		return self::$securityKeys[$key];
	}

	public static function performance($key, $defvalue = false)
	{
		return self::$performance[$key];
	}

	public static function iniSet($key, $value)
	{
		@ini_set($key, $value);
	}
}

require_once 'config/api.php';
require_once 'config/calendar.php';
require_once 'config/config.php';
require_once 'config/debug.php';
require_once 'config/developer.php';
require_once 'config/performance.php';
require_once 'config/secret_keys.php';
require_once 'config/security.php';
require_once 'config/version.php';

AppConfig::load('calendar', $CALENDAR_CONFIG);
AppConfig::load('debug', $DEBUG_CONFIG);
AppConfig::load('developer', $DEVELOPER_CONFIG);
AppConfig::load('security', $SECURITY_CONFIG);
AppConfig::load('securityKeys', $SECURITY_KEYS_CONFIG);
AppConfig::load('performance', $PERFORMANCE_CONFIG);
session_save_path($root_directory . '/cache/session');

