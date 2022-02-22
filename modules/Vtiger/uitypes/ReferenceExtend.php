<?php

/**
 * UIType Reference extend Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceExtend_UIType extends Vtiger_Reference_UIType
{
	/** {@inheritdoc} */
	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel(4);
		return empty($modules) ? [] : array_keys($modules);
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		if (\App\Config::performance('SEARCH_REFERENCE_BY_AJAX')) {
			return 'List/Field/Reference.tpl';
		}
		return Vtiger_Base_UIType::getListSearchTemplateName();
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}
}
