<?php

/**
 * EmailTemplates field model class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class EmailTemplates_Field_Model extends Vtiger_Field_Model
{

	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return array List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getModulesListValues()
	{
		$modules = parent::getModulesListValues();
		$modules[App\Module::getModuleId('Reports')] = ['name' => 'Reports', 'label' => \App\Language::translate('Reports', 'Reports')];
		$modules[App\Module::getModuleId('Users')] = ['name' => 'Users', 'label' => \App\Language::translate('Users', 'Users')];
		$modules[App\Module::getModuleId('Events')] = ['name' => 'Events', 'label' => \App\Language::translate('Events', 'Events')];
		$modules[App\Module::getModuleId('ModComments')] = ['name' => 'ModComments', 'label' => \App\Language::translate('ModComments')];
		return $modules;
	}
}
