<?php

/**
 * Action to get markers
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_GetMarkers_Action extends Vtiger_BasicAjax_Action {

	public function checkPermission(Vtiger_Request $request) {
		return true;
	}

	
	public function process(Vtiger_Request $request) {
		$records = $this->getRecordIds($request);
		$data = [];
		foreach($records as $recordId){
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$data = array_merge($data, OpenStreetMap_Module_Model::readCoordinates($recordId));
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
	protected function getRecordIds(Vtiger_Request $request)
	{
		$cvId = $request->get('viewname');
		$module = $request->get('srcModule');
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

}
