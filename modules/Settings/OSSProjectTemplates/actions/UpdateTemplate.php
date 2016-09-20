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

class Settings_OSSProjectTemplates_UpdateTemplate_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{

		$baseModuleName = $request->get('base_module');
		$id = $request->get('tpl_id');
		$db = PearDatabase::getInstance();

		$settingsModuleModel = Settings_Vtiger_Module_Model::getInstance('Settings:OSSProjectTemplates');
		$fieldTab = $settingsModuleModel->getConfigurationForModule($baseModuleName);
		$parent = $request->get('parent_tpl_id');
		if (!$parent) {
			$parent = 0;
		}

		if ($fieldTab && count($fieldTab)) {
			foreach ($fieldTab as $key => $value) {
				$valField = $request->get($key);
				$sql = "UPDATE vtiger_oss_project_templates SET fld_val = ? WHERE id_tpl = ? && fld_name = ? && module = ?";

				if (is_array($valField)) {
					$db->pquery($sql, array(json_encode($valField), $id, $key, $baseModuleName), true);
				} else {
					$db->pquery($sql, array($valField, $id, $key, $baseModuleName), true);
				}

				$dateDayInterval = $request->get($key . '_day');
				$dateDayIntervalType = $request->get($key . '_day_type');

				if ($dateDayInterval) {
					$sql = "UPDATE vtiger_oss_project_templates SET fld_val = '$dateDayInterval' WHERE id_tpl = $id && fld_name = '{$key}_day' && module = '$baseModuleName'";
					$db->query($sql, true);

					$sql = "SELECT `fld_val` FROM `vtiger_oss_project_templates` WHERE `id_tpl` = $id && `fld_name` = '{$key}_day' && `module` = '$baseModuleName'";
					$result = $db->query($sql, true);

					if ($db->num_rows($result) == 0) {

						$sql = "INSERT INTO vtiger_oss_project_templates VALUES ('', '{$key}_day', $dateDayInterval, $id, '$parent', '$baseModuleName' )";
						$result = $db->query($sql, true);
					}
				}
				if (!!$dateDayIntervalType) {
					$sql = "DELETE FROM vtiger_oss_project_templates WHERE id_tpl = $id && fld_name = '{$key}_day_type' && module = '$baseModuleName'";
					$db->query($sql, true);

					//  $lastTplId = $this->getLastTplId($baseModuleName);
					//  $parentTplId = vtlib\Functions::getSingleFieldValue('vtiger_oss_project_templates', 'parent', 'id_tpl', $id);
					$sql = "INSERT INTO vtiger_oss_project_templates VALUES(NULL, '{$key}_day_type', '$dateDayIntervalType', $id, '$parent', '$baseModuleName')";
					$db->query($sql, true);
				} else {
					$sql = "DELETE FROM vtiger_oss_project_templates WHERE id_tpl = $id && fld_name = '{$key}_day_type' && module = '$baseModuleName'";
					$db->query($sql, true);
				}
			}

			$sql = "UPDATE vtiger_oss_project_templates SET fld_val = '{$request->get('tpl_name')}' WHERE id_tpl = $id && fld_name = 'tpl_name'";
			$db->query($sql, true);
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
