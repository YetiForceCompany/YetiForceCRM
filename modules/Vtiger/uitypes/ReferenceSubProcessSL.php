<?php

/**
 * UIType Reference subprocess second level Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_ReferenceSubProcessSL_UIType.
 */
class Vtiger_ReferenceSubProcessSL_UIType extends Vtiger_ReferenceLink_UIType
{
	/** {@inheritdoc} */
	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel(3);
		return empty($modules) ? [] : array_keys($modules);
	}

	/** {@inheritdoc} */
	public function getParentModule($module)
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel(3);
		return $modules[$module]['parentModule'] ?? '';
	}
}
