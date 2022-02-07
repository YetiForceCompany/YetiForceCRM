<?php

/**
 * UIType ReferenceSubProcess Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceSubProcess_UIType extends Vtiger_ReferenceLink_UIType
{
	/** {@inheritdoc} */
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

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/ReferenceSubProcess.tpl';
	}
}
