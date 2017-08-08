<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */

/**
 * Class VTExpressionsManager
 */
class VTExpressionsManager
{

	function __construct($adb)
	{
		$this->adb = $adb;
	}

	/**
	 * Cache array
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Add parameter to cache
	 * @param string $key
	 * @param mixed $value
	 */
	static function addToCache($key, $value)
	{
		self::$cache[$key] = $value;
	}

	/**
	 * Get parameter from cache
	 * @param string $key
	 * @return mixed|boolean
	 */
	static function fromCache($key)
	{
		if (isset(self::$cache[$key]))
			return self::$cache[$key];
		return false;
	}

	/**
	 * Clear cache array
	 */
	static function clearCache()
	{
		self::$cache = [];
	}

	/**
	 * Get fields info
	 * @param string $moduleName
	 * @return array
	 */
	function fields($moduleName)
	{
		$current_user = vglobal('current_user');
		$result = vtws_describe($moduleName, $current_user);
		$fields = $result['fields'];
		$arr = [];
		foreach ($fields as $field) {
			$arr[$field['name']] = $field['label'];
		}
		return $arr;
	}

	/**
	 * Get expression functions
	 * @return array
	 */
	function expressionFunctions()
	{
		return ['concat' => 'concat(a,b)', 'time_diffdays(a,b)' => 'time_diffdays(a,b)', 'time_diffdays(a)' => 'time_diffdays(a)', 'time_diff(a,b)' => 'time_diff(a,b)', 'time_diff(a)' => 'time_diff(a)',
			'add_days' => 'add_days(datefield, noofdays)', 'sub_days' => 'sub_days(datefield, noofdays)', 'add_time(timefield, minutes)' => 'add_time(timefield, minutes)', 'sub_time(timefield, minutes)' => 'sub_time(timefield, minutes)',
			'today' => "get_date('today')", 'tomorrow' => "get_date('tomorrow')", 'yesterday' => "get_date('yesterday')"];
	}
}
