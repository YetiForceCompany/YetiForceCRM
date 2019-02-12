<?php

/**
 * EmailTemplates field model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class EmailTemplates_Field_Model extends Vtiger_Field_Model
{
	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return array List of picklist values if the field is of type picklist or multipicklist, null otherwise
	 */
	public function getModulesListValues()
	{
		$modules = parent::getModulesListValues();
		$modules[App\Module::getModuleId('Users')] = ['name' => 'Users', 'label' => \App\Language::translate('Users', 'Users')];
		$modules[App\Module::getModuleId('ModComments')] = ['name' => 'ModComments', 'label' => \App\Language::translate('ModComments')];

		return $modules;
	}
}
