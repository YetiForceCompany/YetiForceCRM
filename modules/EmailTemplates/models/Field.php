<?php

/**
 * EmailTemplates field model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class EmailTemplates_Field_Model extends Vtiger_Field_Model
{
	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isViewEnabled()
	{
		return 'sys_name' !== $this->getName() && parent::isViewEnabled();
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
