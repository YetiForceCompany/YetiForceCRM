<?php
/**
 * Web service module file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core;

/**
 * Web service module class.
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
	public static function getPermittedModules(): array
	{
		if (isset(static::$permittedModules)) {
			return static::$permittedModules;
		}
		$modules = [];
		foreach (\vtlib\Functions::getAllModules(false, false, 0) as $value) {
			if (\Api\WebservicePremium\Privilege::isPermitted($value['name'])) {
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
	public static function checkModuleAccess($moduleName): bool
	{
		if (isset(static::$permittedModules)) {
			return isset(static::$permittedModules[$moduleName]);
		}
		return \Api\WebservicePremium\Privilege::isPermitted($moduleName);
	}

	/**
	 * Returns info about field to permission for record.
	 *
	 * @param string $moduleName
	 * @param int    $serverId
	 *
	 * @return array|false
	 */
	public static function getApiFieldPermission(string $moduleName, int $serverId)
	{
		$cacheName = $moduleName . $serverId;
		if (\App\Cache::has('API-FieldPermission', $cacheName)) {
			return \App\Cache::get('API-FieldPermission', $cacheName);
		}
		$fieldInfo = (new \App\Db\Query())->from('vtiger_field')->where([
			'tabid' => \App\Module::getModuleId($moduleName),
			'uitype' => 318,
			'fieldparams' => $serverId
		])->one();
		\App\Cache::save('API-FieldPermission', $cacheName, $fieldInfo, \App\Cache::LONG);
		return $fieldInfo;
	}
}
