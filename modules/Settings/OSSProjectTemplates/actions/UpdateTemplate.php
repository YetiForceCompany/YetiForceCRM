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
		$db = App\Db::getInstance();

		$settingsModuleModel = Settings_Vtiger_Module_Model::getInstance('Settings:OSSProjectTemplates');
		$fieldTab = $settingsModuleModel->getConfigurationForModule($baseModuleName);
		$parent = $request->get('parent_tpl_id');
		if (!$parent) {
			$parent = 0;
		}

		if ($fieldTab && count($fieldTab)) {
			foreach ($fieldTab as $key => $value) {
				$valField = $request->get($key);
				$db->createCommand()
					->update('vtiger_oss_project_templates', ['fld_val' => is_array($valField) ? json_encode($valField) : $valField], ['id_tpl' => $id, 'fld_name' => $key, 'module' => $baseModuleName])
					->execute();
				$dateDayInterval = $request->get($key . '_day');
				$dateDayIntervalType = $request->get($key . '_day_type');

				if ($dateDayInterval) {
					$db->createCommand()->update('vtiger_oss_project_templates', ['fld_val' => $dateDayInterval], ['id_tpl' => $id, 'fld_name' => $key . '_day', 'module' => $baseModuleName])
						->execute();
					$isExists = (new \App\Db\Query())
						->from('vtiger_oss_project_templates')
						->where(['id_tpl' => $id, 'fld_name' => $key . '_day', 'module' => $baseModuleName])
						->exists();
					if (!$isExists) {
						$db->createCommand()->insert('vtiger_oss_project_templates', [
							'fld_name' => $key . '_day',
							'fld_val' => $dateDayInterval,
							'id_tpl' => $id,
							'parent' => $parent,
							'module' => $baseModuleName
						])->execute();
					}
				}
				if (!!$dateDayIntervalType) {
					$db->createCommand()
						->delete('vtiger_oss_project_templates', ['id_tpl' => $id, 'fld_name' => $key . '_day_type', 'module' => $baseModuleName])
						->execute();
					$db->createCommand()->insert('vtiger_oss_project_templates', [
							'fld_name' => $key . '_day_type',
							'fld_val' => $dateDayIntervalType,
							'id_tpl' => $id,
							'parent' => $parent,
							'module' => $baseModuleName
						])->execute();
				} else {
					$db->createCommand()
						->delete('vtiger_oss_project_templates', ['id_tpl' => $id, 'fld_name' => $key . '_day_type', 'module' => $baseModuleName])
						->execute();
				}
			}

			$db->createCommand()
				->update('vtiger_oss_project_templates', ['fld_val' => $request->get('tpl_name')], ['id_tpl' => $id, 'fld_name' => 'tpl_name'])
				->execute();
		}

		$backView = $request->get('back_view');
		$backIdTpl = $request->get('parent_tpl_id');

		header("Location: index.php?module=OSSProjectTemplates&parent=Settings&view=" . $backView . '&tpl_id=' . $backIdTpl);
	}

	public function getLastTplId($moduleName)
	{
		return (new \App\Db\Query())->select(['id_tpl'])
			->from('vtiger_oss_project_templates')
			->orderBy(['id_tpl' => SORT_DESC])
			->limit(1)->scalar();
	}
}
