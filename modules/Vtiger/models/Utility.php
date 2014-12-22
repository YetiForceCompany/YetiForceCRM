<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Action Model Class
 */
class Vtiger_Utility_Model extends Vtiger_Action_Model {

	public function isUtilityTool() {
		return true;
	}

	public function isModuleEnabled($module) {
		$db = PearDatabase::getInstance();
		if(!$module->isEntityModule()) {
            if(!$module->isUtilityActionEnabled())
                return false;
		}
		$tabId = $module->getId();
		$sql = 'SELECT 1 FROM vtiger_profile2utility WHERE tabid = ? AND activityid = ? LIMIT 1';
		$params = array($tabId, $this->getId());
		$result = $db->pquery($sql, $params);
		if($result && $db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

}