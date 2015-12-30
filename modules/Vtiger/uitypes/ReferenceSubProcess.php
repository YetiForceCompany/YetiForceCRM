<?php

/**
 * UIType ReferenceSubProcess Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceSubProcess_UIType extends Vtiger_ReferenceLink_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/ReferenceSubProcess.tpl';
	}

	public function getReferenceList()
	{
		$modules = Vtiger_Module_Model::getModulesByLevel(2);
		return array_keys($modules);
	}

	public function getParentModule($module)
	{
		$modules = Vtiger_Module_Model::getModulesByLevel(2);
		if (isset($modules[$module]['parentModule'])) {
			return $modules[$module]['parentModule'];
		}
		return '';
	}
}
