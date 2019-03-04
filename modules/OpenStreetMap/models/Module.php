<?php

/**
 * Module Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Check if module is allowed.
	 *
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public function isAllowModules($moduleName)
	{
		return in_array($moduleName, \App\Config::module($this->getName(), 'ALLOW_MODULES', []));
	}

	/**
	 * Function to get allow modules with checking permissions.
	 *
	 * @return array
	 */
	public function getAllowedModules()
	{
		$allAllowedModules = \App\Config::module($this->getName(), 'ALLOW_MODULES', []);
		foreach ($allAllowedModules as $key => $moduleName) {
			if (!\App\Privilege::isPermitted($moduleName)) {
				unset($allAllowedModules[$key]);
			}
		}
		return $allAllowedModules;
	}
}
