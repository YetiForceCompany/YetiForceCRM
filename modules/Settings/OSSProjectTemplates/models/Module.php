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
			$fieldTab = json_decode($json, true);
			return $fieldTab;
		} else {
			return false;
		}
	}

	public function getListTpl($moduleName, $parentId = 0, $forTpl = false)
	{
		$dataReader = (new \App\Db\Query())->from('vtiger_oss_project_templates')
			->where(['module' => $moduleName, 'parent' => $parentId])
			->createCommand()->query();
		$output = [];

		while($row = $dataReader->read()) {
			$idTpl = $row['id_tpl'];
			$fldName = $row['fld_name'];
			$output[$idTpl][$fldName] = $row['fld_val'];
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

					$countProfile = count($profile);
					for ($i = 0; $i < $countProfile; $i++) {
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
		if (array_key_exists(\App\Module::getModuleId('Project'), $menuModelsList)) {
			unset($output);
		}

		return $output;
	}

	public function getCurrentUserProfile()
	{
		$userModel = Users_Record_Model::getCurrentUserModel();
		$roleId = $userModel->getRole();
		$dataReader = (new \App\Db\Query())->select(['vtiger_profile.profileid'])
			->from('vtiger_profile')
			->leftJoin('vtiger_role2profile', 'vtiger_role2profile.profileid = vtiger_profile.profileid ')
			->where(['vtiger_role2profile.roleid' => $roleId])
			->createCommand()->query();
		$profileList = [];
		while($profileId = $dataReader->readColumn(0)) {
			$profileList[] = str_replace('+', '\\+', $profileId);
		}
		return $profileList;
	}
}
