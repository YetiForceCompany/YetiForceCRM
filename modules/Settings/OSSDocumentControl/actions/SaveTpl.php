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
		$db = App\Db::getInstance();
		$db->createCommand()->insert('vtiger_ossdocumentcontrol', [
			'module_name' => $baseModule,
			'summary' => $summary,
			'doc_folder' => $docFolder,
			'doc_name' => $docName,
			'doc_request' => $docRequest ? 1 : 0,
			'doc_order' => $docOrder
		])->execute();
		$recordId = $db->getLastInsertID();

		$this->addConditions($conditionAll, $recordId);
		$this->addConditions($conditionOption, $recordId, false);

		header("Location: index.php?module=OSSDocumentControl&parent=Settings&view=Index");
	}

	public function addConditions($conditions, $relId, $mendatory = true)
	{
		$db = App\Db::getInstance();
		$conditionObj = json_decode($conditions);
		if (count($conditionObj)) {
			foreach ($conditionObj as $obj) {
				$db->createCommand()->insert('vtiger_ossdocumentcontrol_cnd', [
					'ossdocumentcontrolid' => $relId,
					'fieldname' => $obj->field,
					'comparator' => $obj->name,
					'val' => is_array($obj->val) ? implode('::', $obj->val) : $obj->val,
					'required' => $mendatory,
					'field_type' => $obj->type
				])->execute();
			}
		}
	}
}
