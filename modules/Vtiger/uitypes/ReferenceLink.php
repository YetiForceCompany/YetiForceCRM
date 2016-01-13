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
		$modules = Vtiger_Module_Model::getModulesByLevel();
		return array_keys($modules);
	}

	public function getListSearchTemplateName()
	{
		return Vtiger_Base_UIType::getListSearchTemplateName();
	}
}
