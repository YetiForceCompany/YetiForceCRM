<?php

/**
 * Module Model
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Check if module is allowed
	 * @param string $moduleName
	 * @return boolean
	 */
	public function isAllowModules($moduleName)
	{
		return in_array($moduleName, AppConfig::module($this->getName(), 'ALLOW_MODULES'));
	}

	/**
	 * Function to get allow modules with checking permissions
	 * @return array
	 */
	public function getAllowedModules()
	{
		$allAllowedModules = AppConfig::module($this->getName(), 'ALLOW_MODULES');
		foreach ($allAllowedModules as $key => $moduleName) {
			if (!\App\Privilege::isPermitted($moduleName)) {
				unset($allAllowedModules[$key]);
			}
		}
		return $allAllowedModules;
	}
}
