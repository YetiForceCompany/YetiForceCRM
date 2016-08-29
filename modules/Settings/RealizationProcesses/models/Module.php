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

class Settings_RealizationProcesses_Module_Model extends Settings_Vtiger_Module_Model
{

	/**
	 * Gets Project status 
	 * @return - array of Project status
	 */
	public static function getProjectStatus()
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering Settings_RealizationProcesses_Module_Model::getProjectStatus() method ...");
		$sql = 'SELECT * FROM `vtiger_projectstatus`;';
		$result = $adb->query($sql);
		$rowsNum = $adb->num_rows($result);

		for ($i = 0; $i < $rowsNum; $i++) {
			$return[$i]['id'] = $adb->query_result($result, $i, 'projectstatusid');
			$return[$i]['statusTranslate'] = vtranslate($adb->query_result($result, $i, 'projectstatus'), 'Project');
			$return[$i]['status'] = $adb->query_result($result, $i, 'projectstatus');
		}
		$log->debug("Exiting Settings_RealizationProcesses_Module_Model::getProjectStatus() method ...");
		return $return;
	}

	/**
	 * Gets status
	 * @return - array of status
	 */
	public static function getStatusNotModify()
	{
		$log = vglobal('log');
		$adb = PearDatabase::getInstance();
		$log->debug("Entering Settings_RealizationProcesses_Module_Model::getStatusNotModify() method ...");
		$sql = 'SELECT * FROM `vtiger_realization_process`;';
		$result = $adb->query($sql);
		$rowsNum = $adb->num_rows($result);
		for ($i = 0; $i < $rowsNum; $i++) {
			$moduleId = $adb->query_result($result, $i, 'module_id');
			$moduleName = vtlib\Functions::getModuleName($moduleId);
			$return[$moduleName]['id'] = $moduleId;
			$status = \includes\utils\Json::decode(html_entity_decode($adb->query_result($result, $i, 'status_indicate_closing')));
			if (!is_array($status)) {
				$status = [$status];
			}
			$return[$moduleName]['status'] = $status;
		}

		$log->debug("Exiting Settings_RealizationProcesses_Module_Model::getStatusNotModify() method ...");
		return $return;
	}

	/**
	 * Update status
	 * @return - array of status
	 */
	public function updateStatusNotModify($moduleId, $status)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering Settings_RealizationProcesses_Module_Model::updateStatusNotModify() method ...");
		$query = "UPDATE `vtiger_realization_process` SET `status_indicate_closing` = ? WHERE `module_id` = ?";
		$data = \includes\utils\Json::encode($status);
		$adb->pquery($query, array($data, $moduleId));
		$log->debug("Exiting Settings_RealizationProcesses_Module_Model::updateStatusNotModify() method ...");
		return TRUE;
	}
}
