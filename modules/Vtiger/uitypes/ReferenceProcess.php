<?php

/**
 * UIType ReferenceProcess Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceProcess_UIType extends Vtiger_ReferenceLink_UIType
{
	/** {@inheritdoc} */
	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel(1);
		if (!empty($modules)) {
			return array_keys($modules);
		}
		return [];
	}
}
