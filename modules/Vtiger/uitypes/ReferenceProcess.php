<?php

/**
 * UIType ReferenceProcess Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceProcess_UIType extends Vtiger_ReferenceLink_UIType
{

	public function getReferenceList()
	{
		$modules = \App\ModuleHierarchy::getModulesByLevel(1);
		if (!empty($modules)) {
			return array_keys($modules);
		}
		return [];
	}
}
