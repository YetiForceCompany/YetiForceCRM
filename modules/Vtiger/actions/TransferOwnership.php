<?php

/**
 * Vtiger TransferOwnership action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_TransferOwnership_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($moduleName, 'EditView') || !$userPriviligesModel->hasModuleActionPermission($moduleName, 'MassTransferOwnership')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$module = $request->getModule();
		$transferOwnerId = $request->getInteger('transferOwnerId');
		$record = $request->getInteger('record');
		$relatedModules = $request->getByType('related_modules', 'Text');
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'TransferOwnership', $module);
		$transferModel = new $modelClassName();
		if (empty($record)) {
			$recordIds = $this->getBaseModuleRecordIds($request);
		} else {
			$recordIds = [$record];
		}
		$configMaxTransferRecords = App\Config::performance('maxMassTransferOwnershipRecords');
		if (count($recordIds) > $configMaxTransferRecords) {
			$response = new Vtiger_Response();
			$response->setResult(['notify' => ['text' => \App\Language::translateArgs('LBL_SELECT_UP_TO_RECORDS', '_Base', $configMaxTransferRecords), 'type' => 'error']]);
			$response->emit();
			return;
		}
		if (!empty($recordIds)) {
			$transferModel->transferRecordsOwnership($module, $transferOwnerId, $recordIds);
		}
		if (!empty($relatedModules)) {
			foreach ($relatedModules as $relatedData) {
				$explodedData = explode('::', $relatedData);
				$relatedModule = current($explodedData);
				$relatedModuleRecordIds = $transferModel->getRelatedModuleRecordIds($request, $recordIds, $relatedData);
				if (!empty($relatedModuleRecordIds)) {
					$transferModel->transferRecordsOwnership($relatedModule, $transferOwnerId, $relatedModuleRecordIds);
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	protected function getBaseModuleRecordIds(\App\Request $request)
	{
		$cvId = $request->getByType('viewname', 2);
		$module = $request->getModule();
		$selectedIds = $request->getArray('selected_ids', 2);
		$excludedIds = $request->getArray('excluded_ids', 2);

		if (!empty($selectedIds) && $selectedIds[0] !== 'all' && !empty($selectedIds) && count($selectedIds) > 0) {
			foreach ($selectedIds as $key => &$recordId) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
				if (!$recordModel->isEditable()) {
					unset($selectedIds[$key]);
				}
			}

			return $selectedIds;
		}

		if ($selectedIds[0] == 'all') {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
			if ($customViewModel) {
				$searchKey = $request->getByType('search_key', 'Alnum');
				$operator = $request->getByType('operator');
				if (!empty($operator)) {
					$customViewModel->set('operator', $operator);
					$customViewModel->set('search_key', $searchKey);
					$customViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $module, $searchKey, $operator));
				}
				$customViewModel->set('search_params', App\Condition::validSearchParams($module, $request->getArray('search_params')));
				return $customViewModel->getRecordIds($excludedIds, $module, true);
			}
		}
		return [];
	}
}
