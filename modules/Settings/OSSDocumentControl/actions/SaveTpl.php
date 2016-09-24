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

class Settings_OSSDocumentControl_SaveTpl_Action extends Settings_Vtiger_Index_Action
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
		$docOrder = $request->get('doc_order');

		$conditionAll = $request->getRaw('condition_all_json');
		$conditionOption = $request->getRaw('condition_option_json');

		$db = PearDatabase::getInstance();

		$insertBaseRecord = "INSERT INTO vtiger_ossdocumentcontrol VALUES(?, ?, ?, ?, ?, ?, ?)";
		$db->pquery($insertBaseRecord, array(NULL, $baseModule, $summary, $docFolder, $docName, $docRequest, $docOrder), true);
		$recordId = $db->getLastInsertID();

		$this->addConditions($conditionAll, $recordId);
		$this->addConditions($conditionOption, $recordId, FALSE);

		header("Location: index.php?module=OSSDocumentControl&parent=Settings&view=Index");
	}

	public function addConditions($conditions, $relId, $mendatory = TRUE)
	{
		$db = PearDatabase::getInstance();

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
