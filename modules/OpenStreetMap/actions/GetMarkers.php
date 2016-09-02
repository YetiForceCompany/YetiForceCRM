<?php

/**
 * Action to get markers
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_GetMarkers_Action extends Vtiger_BasicAjax_Action
{

	public function process(Vtiger_Request $request)
	{
		$records = $this->getRecordIds($request);
		$sourceModule = $request->get('srcModule');
		$srcModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		if (!$moduleModel->isAllowModules($sourceModule)) {
			$modelFields = $srcModuleModel->getFields();
			$referenceListModules = [];
			$referenceField = false;
			foreach ($modelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList)) {
						foreach ($referenceList as $referenceModule) {
							$fieldMap[$referenceModule] = $fieldName;
						}
					}
				}
			}

			$records = $this->getRecordsForParentModule($srcModuleModel, $moduleModel, $records, $fieldMap);
		}
		$data = [];
		foreach ($records as $recordId) {
			$data = array_merge($data, OpenStreetMap_Module_Model::readCoordinates($recordId));
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}

	private function getRecordsForParentModule($srcModuleModel, $moduleModel, $records, $fieldMap)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserModel();
		$recordsToReturn = [];
		foreach ($fieldMap as $referenceModule => $fieldName) {
			if ($moduleModel->isAllowModules($referenceModule)) {
				$queryGenerator = new QueryGenerator($srcModuleModel->getName(), $currentUserModel);
				$queryGenerator->setFields([$fieldName]);
				$queryGenerator->setCustomCondition([
					'tablename' => 'vtiger_crmentity',
					'column' => 'crmid',
					'operator' => 'IN',
					'value' => '(' . implode(',', $records) . ')',
					'glue' => 'AND'
				]);
				$query = $queryGenerator->getQuery();
				$db = PearDatabase::getInstance();
				$result = $db->query($query);
				while ($row = $db->getRow($result)) {
					if(!empty($row[$fieldName]))
						$recordsToReturn [$row[$fieldName]] = $row[$fieldName];
				}
			}
		}
		return $recordsToReturn;
	}

	private function getRecordIds(Vtiger_Request $request)
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
				return $customViewModel->getRecordIds($excludedIds, $module, false);
			}
		}
		return [];
	}
}
