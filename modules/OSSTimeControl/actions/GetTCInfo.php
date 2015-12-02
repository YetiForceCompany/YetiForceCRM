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

class OSSTimeControl_GetTCInfo_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$moduleName = $request->getModule();

		$id = $request->get('id');
		$sourceModule = $request->get('sourceModule');

		$sourceData = array();

		if (isRecordExists($id)) {
			$record = Vtiger_Record_Model::getInstanceById($id, $sourceModule);
			$entity = $record->getEntity();
			$sourceData = $entity->column_fields;
			if ($sourceModule == 'HelpDesk') {
				$sourceData['contact_label'] = Vtiger_Functions::getCRMRecordLabel($sourceData['contact_id']);
				if (Vtiger_Functions::getCRMRecordType($sourceData['parent_id']) != 'Accounts')
					unset($sourceData['parent_id']);
				else
					$sourceData['account_label'] = Vtiger_Functions::getCRMRecordLabel($sourceData['parent_id']);
			} else if ($sourceModule == 'Project') {
				$ifExist = $adb->query("select * from vtiger_account where accountid = " . $sourceData['linktoaccountscontacts'] . "", true, "Błąd podczas pobierania danych z vtiger_crmentityrel");
				if ($adb->num_rows($ifExist) > 0)
					$sourceData['account_label'] = Vtiger_Functions::getCRMRecordLabel($sourceData['linktoaccountscontacts']);
				else
					$sourceData['contact_label'] = Vtiger_Functions::getCRMRecordLabel($sourceData['linktoaccountscontacts']);
			}
		}

		if ($sourceData === false) {
			$result = array('success' => false, 'message' => vtranslate('LBL_FAILED_TO_IMPORT_INFO', $moduleName));
		} else {
			$result = array('success' => true, 'sourceData' => $sourceData);
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
