<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Vtiger_TransferOwnership_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'EditView') || !$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'MassTransferOwnership')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$module = $request->getModule();
		$transferOwnerId = $request->get('transferOwnerId');
		$record = $request->get('record');
		$relatedModules = $request->get('related_modules');
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TransferOwnership', $module);
		$transferModel = new $modelClassName();

		if (empty($record))
			$recordIds = $this->getBaseModuleRecordIds($request);
		else
			$recordIds = [$record];
		if (!empty($recordIds)) {
			$transferModel->transferRecordsOwnership($module, $transferOwnerId, $recordIds);
		}
		if (!empty($relatedModules)) {
			foreach ($relatedModules as $relatedData) {
				$relatedModule = reset(explode('::', $relatedData));
				$relatedModuleRecordIds = $transferModel->getRelatedModuleRecordIds($request, $recordIds, $relatedData);
				if (!empty($relatedModuleRecordIds)) {
					$transferModel->transferRecordsOwnership($relatedModule, $transferOwnerId, $relatedModuleRecordIds);
				}
			}
		}
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
				foreach ($selectedIds as $key => &$recordId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
					if (!$recordModel->isEditable()) {
						unset($selectedIds[$key]);
					}
				}
				return $selectedIds;
			}
		}

		if ($selectedIds == 'all') {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
			if ($customViewModel) {
				$searchKey = $request->get('search_key');
				$searchValue = $request->get('search_value');
				$operator = $request->get('operator');
				if (!empty($operator)) {
					$customViewModel->set('operator', $operator);
					$customViewModel->set('search_key', $searchKey);
					$customViewModel->set('search_value', $searchValue);
				}

				$customViewModel->set('search_params', $request->get('search_params'));
				return $customViewModel->getRecordIds($excludedIds, $module, true);
			}
		}
		return [];
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
