<?php

/**
 * UIType ReferenceLink Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel();
		return array_keys($modules);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getListSearchTemplateName()
	{
		if (AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			return 'uitypes/ReferenceSearchView.tpl';
		}
		return Vtiger_Base_UIType::getListSearchTemplateName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAjaxEditable()
	{
		return false;
	}
}
