<?php

/**
 * UIType ReferenceLink Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceLink_UIType extends Vtiger_Reference_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
		}
		$this->validate = true;
	}

	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel();
		return array_keys($modules);
	}

	public function getListSearchTemplateName()
	{
		if (AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			return 'uitypes/ReferenceSearchView.tpl';
		}
		return Vtiger_Base_UIType::getListSearchTemplateName();
	}

	public function isAjaxEditable()
	{
		return false;
	}
}
