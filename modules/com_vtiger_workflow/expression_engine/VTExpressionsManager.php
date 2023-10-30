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
 * Class VTExpressionsManager.
 */
class VTExpressionsManager
{
	/**
	 * Cache array.
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Add parameter to cache.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function addToCache($key, $value)
	{
		self::$cache[$key] = $value;
	}

	/**
	 * Get parameter from cache.
	 *
	 * @param string $key
	 *
	 * @return mixed|bool
	 */
	public static function fromCache($key)
	{
		if (isset(self::$cache[$key])) {
			return self::$cache[$key];
		}
		return false;
	}

	/**
	 * Clear cache array.
	 *
	 * @return void
	 */
	public static function clearCache(): void
	{
		self::$cache = [];
	}

	/**
	 * Get fields info.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function fields($moduleName)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$arr = [];
		foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
			$arr[$fieldName] = $fieldModel->getFieldLabel();
		}
		return $arr;
	}

	/**
	 * Get expression functions.
	 *
	 * @return array
	 */
	public function expressionFunctions()
	{
		return ['concat' => 'concat(a,b)', 'time_diffdays(a,b)' => 'time_diffdays(a,b)', 'time_diffdays(a)' => 'time_diffdays(a)', 'time_diff(a,b)' => 'time_diff(a,b)', 'time_diff(a)' => 'time_diff(a)',
			'add_days' => 'add_days(datefield, noofdays)', 'sub_days' => 'sub_days(datefield, noofdays)', 'add_time(timefield, minutes)' => 'add_time(timefield, minutes)', 'sub_time(timefield, minutes)' => 'sub_time(timefield, minutes)',
			'today' => "get_date('today')", 'tomorrow' => "get_date('tomorrow')", 'yesterday' => "get_date('yesterday')", ];
	}
}
