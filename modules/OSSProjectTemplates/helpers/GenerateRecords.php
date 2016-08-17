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

class GenerateRecords
{

	private $moduleModel = false;

	public function generate($templateId, $relId)
	{
		$this->moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:OSSProjectTemplates');

		$baseId = $this->generateBaseRecord($templateId, $relId);
		$this->generateChildRecords($templateId, $baseId, $relId);
		return $baseId;
	}

	private function generateChildRecords($templateId, $baseRecord, $relId)
	{
		$listTpl = $this->getListOfChildTpl($templateId);
		for ($i = 0; $i < count($listTpl); $i++) {
			$moduleType = $this->templateModuleType($listTpl[$i]);

			require_once "modules/$moduleType/$moduleType.php";
			$childeModule = new $moduleType();

			$moduleConfig = $this->moduleModel->getConfigurationForModule($moduleType);

			foreach ($moduleConfig as $key => $value) {
				require_once "modules/OSSProjectTemplates/fields_action/$value.php";
				$fieldClass = Field_Model_ . $value;
				$field = new $fieldClass();
				$childeModule->column_fields[$key] = $field->getValue($key, $relId, $templateId, $baseRecord, $listTpl[$i]);
			}
			$childeModule->save($moduleType);
		}
	}

	private function generateBaseRecord($templateId, $relId = NULL)
	{
		$moduleType = $this->templateModuleType($templateId);

		require_once "modules/$moduleType/$moduleType.php";
		$baseModule = new $moduleType();

		$moduleConfig = $this->moduleModel->getConfigurationForModule($moduleType);

		foreach ($moduleConfig as $key => $value) {
			require_once "modules/OSSProjectTemplates/fields_action/$value.php";
			$fieldClass = Field_Model_ . $value;
			$field = new $fieldClass();
			$baseModule->column_fields[$key] = $field->getValue($key, $relId, $templateId);
		}
		$baseModule->save($moduleType);
		$id = $baseModule->id;

		if (!!$relId) {
			$this->setRel($relId, vtlib\Functions::getCRMRecordType($relId), $id, $moduleType);
		}
		return $id;
	}

	private function templateModuleType($id)
	{
		$db = PearDatabase::getInstance();

		$getModuleTypeSql = "SELECT * FROM vtiger_oss_project_templates WHERE id_tpl = ?";
		$getModuleTypeResult = $db->pquery($getModuleTypeSql, array($id), true);

		return $db->query_result($getModuleTypeResult, 0, 'module');
	}

	private function setRel($crmid, $crmModule, $relId, $relModule)
	{
		$db = PearDatabase::getInstance();

		$sql = "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)";
		$db->pquery($sql, array($crmid, $crmModule, $relId, $relModule), true);
	}

	public function updateRel($recordId, $relId)
	{
		$db = PearDatabase::getInstance();
		$db->query("UPDATE vtiger_project SET linktoaccountscontacts = $relId WHERE projectid = $recordId", true);
	}

	private function getListOfChildTpl($templateId)
	{
		$db = PearDatabase::getInstance();

		$getListTplSql = "SELECT DISTINCT id_tpl, parent, module FROM vtiger_oss_project_templates where parent = ?";
		$getListTplResult = $db->pquery($getListTplSql, array($templateId), true);

		$tpl = array();

		for ($i = 0; $i < $db->num_rows($getListTplResult); $i++) {
			$tpl[] = $db->query_result($getListTplResult, $i, 'id_tpl');
		}

		return $tpl;
	}
}
