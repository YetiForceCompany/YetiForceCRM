<?php

/**
 * UIType ReferenceSubProcess Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceSubProcess_UIType extends Vtiger_ReferenceLink_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel(2);
		if (!empty($modules)) {
			return array_keys($modules);
		}
		return [];
	}

	public function getParentModule($module)
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel(2);
		if (isset($modules[$module]['parentModule'])) {
			return $modules[$module]['parentModule'];
		}
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/ReferenceSubProcess.tpl';
	}
}
