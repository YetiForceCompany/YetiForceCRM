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

class Settings_OSSProjectTemplates_CreateTemplate_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{

		$baseModuleName = $request->get('base_module');
		$db = PearDatabase::getInstance();
		$parentTplId = $request->get('parent_tpl_id');

		if (!$parentTplId) {
			$parentTplId = 0;
		}

		$settingsModuleModel = Settings_Vtiger_Module_Model::getInstance('Settings:OSSProjectTemplates');
		$fieldTab = $settingsModuleModel->getConfigurationForModule($baseModuleName);
		$fieldTab['tpl_name'] = '';
		$lastTplId = $this->getLastTplId($baseModuleName);
		$lastTplId++;

		if ($fieldTab && count($fieldTab)) {
			foreach ($fieldTab as $key => $value) {
				$valField = $request->get($key);

				$sql = "INSERT INTO vtiger_oss_project_templates VALUES(?, ?, ?, ?, ?, ?)";

				if (is_array($valField)) {
					$db->pquery($sql, array(NULL, $key, json_encode($valField), $lastTplId, $parentTplId, $baseModuleName), true);
				} else {
					$db->pquery($sql, array(NULL, $key, $valField, $lastTplId, $parentTplId, $baseModuleName), true);
				}

				$dateDayInterval = $request->get($key . '_day');
				$dateDayIntervalType = $request->get($key . '_day_type');

				if ($dateDayInterval) {
					$sql = "INSERT INTO vtiger_oss_project_templates VALUES(NULL, '{$key}_day', '$dateDayInterval', $lastTplId, $parentTplId, '$baseModuleName')";
					$db->query($sql, true);
				}

				if ($dateDayIntervalType) {
					$sql = "INSERT INTO vtiger_oss_project_templates VALUES(NULL, '{$key}_day_type', '$dateDayIntervalType', $lastTplId, $parentTplId, '$baseModuleName')";
					$db->query($sql, true);
				}
			}
		}

		$backView = $request->get('back_view');
		$backIdTpl = $request->get('parent_tpl_id');

		header("Location: index.php?module=OSSProjectTemplates&parent=Settings&view=" . $backView . '&tpl_id=' . $backIdTpl);
	}

	public function getLastTplId($moduleName)
	{
		$db = PearDatabase::getInstance();

		$sql = "SELECT id_tpl FROM vtiger_oss_project_templates order by id_tpl desc limit 0,1";
		$result = $db->query($sql, true);
		return $db->query_result($result, 0, 'id_tpl');
	}
}
