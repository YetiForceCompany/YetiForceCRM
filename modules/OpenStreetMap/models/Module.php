<?php

/**
 * Module Model.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		return \in_array($moduleName, \App\Config::module($this->getName(), 'mapModules', []));
	}

	/**
	 * Function to get allow modules with checking permissions.
	 *
	 * @return string[]
	 */
	public function getAllowedModules(): array
	{
		$allAllowedModules = \App\Config::module($this->getName(), 'mapModules', []);
		foreach ($allAllowedModules as $key => $moduleName) {
			if (!\App\Privilege::isPermitted($moduleName)) {
				unset($allAllowedModules[$key]);
			}
		}
		return $allAllowedModules;
	}
}
