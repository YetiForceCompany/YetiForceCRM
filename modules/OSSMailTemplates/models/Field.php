<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMailTemplates_Field_Model extends Vtiger_Field_Model
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
		$modules[App\Module::getModuleId('Users')] = ['name' => 'Users', 'label' => \App\Language::translate('Users', 'Users')];
		if ($this->getName() === 'oss_module_list') {
			$modules[0] = ['name' => 'System', 'label' => \App\Language::translate('PLL_SYSTEM', $this->getModuleName())];
		}
		return $modules;
	}
}
