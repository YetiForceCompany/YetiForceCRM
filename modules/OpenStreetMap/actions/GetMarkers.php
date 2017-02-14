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
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->set('srcModuleModel', $srcModuleModel);
		$coordinatesModel->set('radius', (int) $request->get('radius'));
		$coordinatesModel->set('selectedIds', $request->get('selected_ids'));
		$coordinatesModel->set('viewname', $request->get('viewname'));
		$coordinatesModel->set('excludedIds', $request->get('excluded_ids'));
		$coordinatesModel->set('searchKey', $request->get('search_key'));
		$coordinatesModel->set('operator', $request->get('operator'));
		$coordinatesModel->set('groupBy', $request->get('groupBy'));
		$coordinatesModel->set('searchValue', $request->get('searchValue'));
		$coordinatesModel->set('search_value', $request->get('search_value'));
		$coordinatesModel->set('lon', $request->get('lon'));
		$coordinatesModel->set('lat', $request->get('lat'));
		$coordinatesModel->set('cache', $request->get('cache'));
		$coordinatesModel->set('search_params', $request->get('search_params'));
		$coordinatesModel->set('request', $request);

		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$coordinatesCenter = $coordinatesModel->getCoordinatesCenter();
		if ($moduleModel->isAllowModules($sourceModule) && !$request->isEmpty('viewname')) {
			$data ['coordinates'] = $coordinatesModel->getCoordinatesCustomView();
		}
		if (!$request->isEmpty('cache')) {
			$data['cache'] = $coordinatesModel->readCoordinatesCache();
		}
		if ($request->has('groupBy')) {
			$legend = [];
			foreach (OpenStreetMap_Coordinate_Model::$colors as $key => $value) {
				$legend [] = [
					'value' => vtranslate($key, $sourceModule),
					'color' => $value
				];
			}
			$data ['legend'] = $legend;
		}
		if (!empty($coordinatesCenter)) {
			$data['coordinatesCeneter'] = $coordinatesCenter;
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
