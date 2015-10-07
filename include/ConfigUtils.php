<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class SysDebug
{

	static function get($key, $defvalue = false)
	{
		global $DEBUG_CONFIG;
		if (isset($DEBUG_CONFIG)) {
			if (isset($DEBUG_CONFIG[$key])) {
				return $DEBUG_CONFIG[$key];
			}
		}
		return $defvalue;
	}

	/** Get boolean value */
	static function getBoolean($key, $defvalue = false)
	{
		return self::get($key, $defvalue);
	}
}

class SysDeveloper
{

	static function get($key, $defvalue = FALSE)
	{
		global $DEVELOPER_CONFIG;
		if (isset($DEVELOPER_CONFIG[$key])) {
			return $DEVELOPER_CONFIG[$key];
		}
		return $defvalue;
	}

	/** Get boolean value */
	static function getBoolean($key, $defvalue = FALSE)
	{
		return self::get($key, $defvalue);
	}
}

class SysSecurity
{

	static function get($key, $defvalue = false)
	{
		global $SECURITY_CONFIG;
		if (isset($SECURITY_CONFIG[$key])) {
			return $SECURITY_CONFIG[$key];
		}
		return $defvalue;
	}

	/** Get boolean value */
	static function getBoolean($key, $defvalue = false)
	{
		return self::get($key, $defvalue);
	}
}

/**
 * Performance perference API
 */
class PerformancePrefs
{

	/**
	 * Get performance parameter configured value or default one
	 */
	static function get($key, $defvalue = false)
	{
		global $PERFORMANCE_CONFIG;
		if (isset($PERFORMANCE_CONFIG)) {
			if (isset($PERFORMANCE_CONFIG[$key])) {
				return $PERFORMANCE_CONFIG[$key];
			}
		}
		return $defvalue;
	}

	/** Get boolean value */
	static function getBoolean($key, $defvalue = false)
	{
		return self::get($key, $defvalue);
	}

	/** Get Integer value */
	static function getInteger($key, $defvalue = false)
	{
		return intval(self::get($key, $defvalue));
	}
}
