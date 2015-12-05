<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Vtiger_TransferOwnership_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'EditView') || !$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'MassTransferOwnership')) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$module = $request->getModule();
		$transferOwnerId = $request->get('transferOwnerId');
		$record = $request->get('record');

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TransferOwnership', $module);
		$transferModel = new $modelClassName();

		if (empty($record))
			$recordIds = $this->getBaseModuleRecordIds($request);
		else
			$recordIds[] = $record;
		$relatedModuleRecordIds = $transferModel->getRelatedModuleRecordIds($request, $recordIds);
		$transferRecordIds = array_merge($relatedModuleRecordIds, $recordIds);
		$transferModel->transferRecordsOwnership($module, $transferOwnerId, $transferRecordIds);

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	protected function getBaseModuleRecordIds(Vtiger_Request $request)
	{
		$cvId = $request->get('viewname');
		$module = $request->getModule();
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		if (!empty($selectedIds) && $selectedIds != 'all') {
			if (!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}

		if ($selectedIds == 'all') {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
			if ($customViewModel) {
				return $customViewModel->getRecordIds($excludedIds, $module);
			}
		}
		return array();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
