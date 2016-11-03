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

class Settings_DataAccess_SaveTpl_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{
		$baseModule = $request->get('base_module');
		$summary = $request->get('summary');
		$conditionAll = $request->getRaw('condition_all_json');
		$conditionOption = $request->getRaw('condition_option_json');
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_dataaccess', [
			'module_name' => $baseModule,
			'summary' => $summary
		])->execute();
		$recordId = $db->getLastInsertID('vtiger_dataaccess_dataaccessid_seq');
		Settings_DataAccess_Module_Model::addConditions($conditionAll, $recordId);
		Settings_DataAccess_Module_Model::addConditions($conditionOption, $recordId, false);
		header("Location: index.php?module=DataAccess&parent=Settings&view=Index");
	}
}
