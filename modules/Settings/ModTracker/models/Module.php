<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_ModTracker_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getModTrackerModules($active = false)
	{
		$adb = PearDatabase::getInstance();
		$restrictedModules = array('Emails', 'Integration', 'Dashboard', 'PBXManager', 'vtmessages', 'vttwitter');
		$params = Array(0, 2, 1);
		$params = array_merge($params, $restrictedModules);
		$sql = 'SELECT vtiger_tab.name,vtiger_tab.tabid, vtiger_modtracker_tabs.visible 
				FROM vtiger_tab LEFT JOIN vtiger_modtracker_tabs ON vtiger_tab.tabid = vtiger_modtracker_tabs.tabid
				WHERE vtiger_tab.presence IN (?,?) && vtiger_tab.isentitytype = ? && vtiger_tab.name NOT IN (%s)';
		$sql = sprintf($sql, generateQuestionMarks($restrictedModules));
		if ($active) {
			$sql = ' && vtiger_modtracker_tabs.visible = ?';
			$params[] = 1;
		}
		$result = $adb->pquery($sql, $params);
		$modules = Array();
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$modules[] = array(
				'id' => $row['tabid'],
				'module' => $row['name'],
				'active' => $row['visible'] == 1 ? true : false,
			);
		}
		return $modules;
	}

	public function changeActiveStatus($tabid, $status)
	{
		include_once('modules/ModTracker/ModTracker.php');
		$moduleModTrackerInstance = new ModTracker();
		if ($status)
			$moduleModTrackerInstance->enableTrackingForModule($tabid);
		else
			$moduleModTrackerInstance->disableTrackingForModule($tabid);
	}
}
