<?php

namespace Api\Core;

/**
 * Module class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Module
{
	/** @var array Permitted modules */
	protected static $permittedModules;

	/**
	 * Get permitted modules.
	 *
	 * @return array
	 */
	public static function getPermittedModules()
	{
		if (isset(static::$permittedModules)) {
			return static::$permittedModules;
		}
		$modules = [];
		foreach (\vtlib\Functions::getAllModules(true, false, 0) as $value) {
			if (\App\Privilege::isPermitted($value['name'])) {
				$modules[$value['name']] = \App\Language::translate($value['name'], $value['name']);
			}
		}
		return static::$permittedModules = $modules;
	}

	/**
	 * Check module access.
	 *
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public static function checkModuleAccess($moduleName)
	{
		if (isset(static::$permittedModules)) {
			return isset(static::$permittedModules[$moduleName]);
		}
		return \App\Privilege::isPermitted($moduleName);
	}
}
