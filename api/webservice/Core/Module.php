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
		foreach (\vtlib\Functions::getAllModules(false, false, 0) as $value) {
			if (\Api\Portal\Privilege::isPermitted($value['name'])) {
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
		return \Api\Portal\Privilege::isPermitted($moduleName);
	}

	/**
	 * Returns info about field to permission for record.
	 *
	 * @param string $moduleName
	 * @param int    $serverId
	 *
	 * @return null|array
	 */
	public static function getFieldPermission(string $moduleName, int $serverId)
	{
		$cacheName = $moduleName . $serverId;
		if (\App\Cache::has('API-FieldPermission', $cacheName)) {
			return \App\Cache::get('API-FieldPermission', $cacheName);
		}
		$fieldInfo = (new \App\Db\Query())->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId($moduleName), 'uitype' => 318, 'fieldparams' => $serverId])->one();
		\App\Cache::save('API-FieldPermission', $cacheName, $fieldInfo, \App\Cache::LONG);
		return $fieldInfo;
	}
}
