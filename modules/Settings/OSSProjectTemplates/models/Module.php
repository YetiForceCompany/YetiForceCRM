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

class Settings_OSSProjectTemplates_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getConfigurationForModule($moduleName)
	{

		$path = 'modules/OSSProjectTemplates/config/' . strtolower($moduleName) . '_config.json';

		if (file_exists($path)) {
			$json = file_get_contents($path);
			$fieldTab = json_decode($json, TRUE);
			return $fieldTab;
		} else {
			return false;
		}
	}

	public function getListTpl($moduleName, $parentId = 0, $forTpl = false)
	{
		$db = PearDatabase::getInstance();

		$sql = "SELECT * FROM vtiger_oss_project_templates WHERE module = ? && parent = ?";
		$result = $db->pquery($sql, array($moduleName, $parentId), true);

		$output = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$record = $db->raw_query_result_rowdata($result, $i);
			$idTpl = $record['id_tpl'];
			$fldName = $record['fld_name'];
			$output[$idTpl][$fldName] = $record['fld_val'];
		}

		$userProfileList = $this->getCurrentUserProfile();

		if (count($output) && $forTpl) {
			foreach ($output as $key => $value) {
				$profile = json_decode($output[$key]['oss_project_visibility']);

				if (!is_array($profile)) {
					if (!in_array($profile, $userProfileList)) {
						unset($output[$key]);
					}
				} else {
					$state = array();

					for ($i = 0; $i < count($profile); $i++) {
						if (in_array($profile[$i], $userProfileList)) {
							$state[] = true;
						} else {
							$state[] = false;
						}
					}

					if (!in_array(true, $state)) {
						unset($output[$key]);
					}
				}
			}
		}

		$menuModelsList = Vtiger_Module_Model::getAll([1]);
		if (array_key_exists(\includes\Modules::getModuleId('Project'), $menuModelsList)) {
			unset($output);
		}

		return $output;
	}

	public function getCurrentUserProfile()
	{
		$userModel = Users_Record_Model::getCurrentUserModel();
		$roleId = $userModel->getRole();

		$db = PearDatabase::getInstance();
		$sql = "SELECT p.profileid as id FROM vtiger_profile p LEFT JOIN vtiger_role2profile r ON r.profileid = p.profileid WHERE r.roleid = ?";
		$params = array($roleId);
		$result = $db->pquery($sql, $params, true);

		$profileList = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$profileName = $db->query_result($result, $i, 'id');
			$profileList[] = str_replace('+', '\\+', $profileName);
		}

		return $profileList;
	}
}
