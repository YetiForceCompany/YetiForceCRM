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

class OSSMailView_MassDelete_Action extends Vtiger_Mass_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function preProcess(Vtiger_Request $request)
	{
		return true;
	}

	function postProcess(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = new OSSMailView_Record_Model();
		$recordModel->setModule($moduleName);
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		if ($selectedIds == 'all' && empty($excludedIds)) {
			$recordModel->deleteAllRecords();
		} else {
			$recordIds = $this->getRecordsListFromRequest($request, $recordModel);
			foreach ($recordIds as $recordId) {
				$recordModel = OSSMailView_Record_Model::getInstanceById($recordId);
				$recordModel->delete_rel($recordId);
				$recordModel->delete();
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(array('module' => $moduleName));
		$response->emit();
	}

	public function getRecordsListFromRequest(Vtiger_Request $request, $recordModel)
	{
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		if (!empty($selectedIds) && $selectedIds != 'all') {
			if (!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}
		if (!empty($excludedIds)) {
			$moduleModel = $recordModel->getModule();
			$recordIds = $moduleModel->getRecordIds($excludedIds);
			return $recordIds;
		}
	}
}
