<?php
namespace Api\Core;

/**
 * Module class 
 * @package YetiForce.WebserviceCore
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Module
{

	/** @var array Permitted modules */
	static protected $permittedModules;

	/**
	 * Get permitted modules
	 * @return array
	 */
	public static function getPermittedModules()
	{
		if (isset(static::$permittedModules)) {
			return static::$permittedModules;
		}
		$action = \Api\Controller::getAction();
		//$permissionType = $action->getPermissionType();
		$modules = [];
		foreach (\vtlib\Functions::getAllModules(true, false, 0) as $value) {
			if (\App\Privilege::isPermitted($value['name'])) {
				$modules[$value['name']] = \App\Language::translate($value['name'], $value['name']);
			}
		}
		return static::$permittedModules = $modules;
	}

	/**
	 * Check module access
	 * @param string $moduleName
	 * @return bool
	 */
	public static function checkModuleAccess($moduleName)
	{
		if (!isset(static::$permittedModules)) {
			self::getPermittedModules();
		}
		return isset(static::$permittedModules[$moduleName]);
	}
}
