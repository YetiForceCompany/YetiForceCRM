<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Vtiger Action Model Class.
 */
class Vtiger_Utility_Model extends Vtiger_Action_Model
{
	public function isUtilityTool()
	{
		return true;
	}

	public function isModuleEnabled($module)
	{
		if (!$module->isEntityModule() && !$module->isUtilityActionEnabled()) {
			return false;
		}
		$tabId = $module->getId();

		return (new App\Db\Query())->from('vtiger_profile2utility')
			->where(['tabid' => $tabId, 'activityid' => $this->getId()])
			->exists();
	}
}
