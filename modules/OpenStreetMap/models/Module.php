<?php

/**
 * Module Model.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_Module_Model extends Vtiger_Module_Model
{
	/** @var string Table name of coordinates for records */
	const COORDINATES_TABLE_NAME = 'u_#__openstreetmap';

	/**
	 * Check if module is allowed.
	 *
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public function isAllowModules($moduleName)
	{
		return \in_array($moduleName, \App\Config::module($this->getName(), 'ALLOW_MODULES', []));
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
