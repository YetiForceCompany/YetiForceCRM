<?php

/**
 * UIType ReferenceLink Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceLink_UIType extends Vtiger_Reference_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel();

		return array_keys($modules);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		if (AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			return 'List/Field/Reference.tpl';
		}
		return Vtiger_Base_UIType::getListSearchTemplateName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'];
	}
}
