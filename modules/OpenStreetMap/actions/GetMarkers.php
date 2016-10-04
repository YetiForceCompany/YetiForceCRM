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
		$data = [];
		$sourceModule = $request->get('srcModule');
		$srcModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$coordinatesCenter = [];
		$radius = (int) $request->get('radius');
		$searchValue = $request->get('searchValue');
		if (!empty($searchValue)) {
			$coordinatesCenter = OpenStreetMap_Module_Model::getCoordinatesBySearching($searchValue);
		}
		if ($request->has('lat') && $request->has('lon')) {
			$coordinatesCenter = [
				'lat' => $request->get('lat'),
				'lon' => $request->get('lon')
			];
		}
		if (!$moduleModel->isAllowModules($sourceModule)) {
			$records = $this->getRecordIds($request);
			$coordinates = [];
			$parentRecords = [];
			foreach ($records as $record) {
				$parentRecords [] = Vtiger_ModulesHierarchy_Model::getParentRecord($record, $sourceModule, 2);
			}
			$parentRecords = array_unique($parentRecords);
			foreach ($parentRecords as $parentRecord) {
				if (!empty($parentRecord))
					$coordinates = array_merge($coordinates, OpenStreetMap_Module_Model::readCoordinates($parentRecord));
			}
			$data ['coordinates'] = $coordinates;
		} else {
			$selectedIds = $request->get('selected_ids');
			if ($selectedIds == 'all') {
				$data ['coordinates'] = OpenStreetMap_Module_Model::readAllCoordinatesFromCustomeView($request, $srcModuleModel, $coordinatesCenter, $radius);
			} else if(!empty($selectedIds)) {
				$records = $this->getRecordIds($request);
				$data ['coordinates'] = OpenStreetMap_Module_Model::readAllCoordinates($records, $srcModuleModel, $request->get('groupBy'), $coordinatesCenter, $radius);
			}
		}
		if ($request->has('groupBy')) {
			$legend = [];
			foreach (OpenStreetMap_Module_Model::$colors as $key => $value) {
				$legend [] = [
					'value' => vtranslate($key, $sourceModule),
					'color' => $value
				];
			}
			$data ['legend'] = $legend;
		}
		if(!$request->isEmpty('cache')){
			$data['cache'] = OpenStreetMap_Module_Model::readCoordinatesCache($request->get('cache'), $request->get('groupBy'), $coordinatesCenter, $radius);
		}
		if (!empty($coordinatesCenter)) {
			$data['coordinatesCeneter'] = $coordinatesCenter;
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
					if (!empty($row[$fieldName]))
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
			return $selectedIds;
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
