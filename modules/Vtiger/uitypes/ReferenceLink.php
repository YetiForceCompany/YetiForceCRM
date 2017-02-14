<?php

/**
 * UIType ReferenceLink Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceLink_UIType extends Vtiger_Reference_UIType
{

	public function isAjaxEditable()
	{
		return false;
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
}
