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

class Settings_OSSDocumentControl_UpdateTpl_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{

		$baseModule = $request->get('base_module');
		$summary = $request->get('summary');
		$docFolder = $request->get('doc_folder');
		$docName = $request->get('doc_name');
		$docRequest = $request->get('doc_request');
		$tplId = $request->get('tpl_id');
		$docOrder = (int) $request->get('doc_order');
		//var_dump($docOrder);
		$conditionAll = $request->getRaw('condition_all_json');
		$conditionOption = $request->getRaw('condition_option_json');

		$db = PearDatabase::getInstance();

		$insertBaseRecord = "UPDATE vtiger_ossdocumentcontrol SET module_name = ?, summary = ?, doc_folder = ?, doc_name = ?, doc_request = ?, doc_order = ? WHERE ossdocumentcontrolid = ?";
		$db->pquery($insertBaseRecord, array($baseModule, $summary, $docFolder, $docName, $docRequest, $docOrder, $tplId), true);

		$this->updateConditions($conditionAll, $tplId);
		$this->updateConditions($conditionOption, $tplId, FALSE);

		header("Location: index.php?module=OSSDocumentControl&parent=Settings&view=Index");
	}

	private function updateConditions($conditions, $relId, $mendatory = TRUE)
	{
		$db = PearDatabase::getInstance();

		if ($mendatory) {

			$deleteOldConditionsSql = "DELETE FROM vtiger_ossdocumentcontrol_cnd WHERE ossdocumentcontrolid = ? && required = 1";
		} else {

			$deleteOldConditionsSql = "DELETE FROM vtiger_ossdocumentcontrol_cnd WHERE ossdocumentcontrolid = ? && required = 0";
		}

		$db->pquery($deleteOldConditionsSql, array($relId), TRUE);

		$conditionObj = json_decode($conditions);

		if (count($conditionObj)) {
			foreach ($conditionObj as $key => $obj) {
				$insertConditionSql = "INSERT INTO vtiger_ossdocumentcontrol_cnd VALUES(?, ?, ?, ?, ?, ?, ?)";
				if (is_array($obj->val)) {
					$db->pquery($insertConditionSql, array(NULL, $relId, $obj->field, $obj->name, implode('::', $obj->val), $mendatory, $obj->type), TRUE);
				} else {
					$db->pquery($insertConditionSql, array(NULL, $relId, $obj->field, $obj->name, $obj->val, $mendatory, $obj->type), TRUE);
				}
			}
		}
	}
}
