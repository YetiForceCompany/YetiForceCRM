<?php

/**
 * SRecurringOrders field model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class SRecurringOrders_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return array List of picklist values if the field is of type picklist or multipicklist, null otherwise
	 */
	public function getModulesListValues()
	{
		$modules = parent::getModulesListValues();
		if ($this->getFieldName() !== 'target_module') {
			return $modules;
		}
		foreach ($modules as $id => $module) {
			if ($module['name'] !== 'SSingleOrders') {
				unset($modules[$id]);
			}
		}
		return $modules;
	}
}
