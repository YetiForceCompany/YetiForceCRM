<?php

/**
 * Action to get markers.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_GetMarkers_Action extends Vtiger_BasicAjax_Action
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
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('srcModule') && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('srcModule'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$data = [];
		$sourceModule = $request->getByType('srcModule');
		$srcModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->set('srcModuleModel', $srcModuleModel);
		$coordinatesModel->set('radius', $request->isEmpty('radius', true) ? 0 : $request->getInteger('radius', 0));
		$coordinatesModel->set('selectedIds', $request->getArray('selected_ids'));
		$coordinatesModel->set('viewname', $request->getByType('viewname', 2));
		$coordinatesModel->set('excludedIds', $request->getArray('excluded_ids'));
		$coordinatesModel->set('searchKey', $request->get('search_key'));
		$coordinatesModel->set('operator', $request->getByType('operator', 1));
		$coordinatesModel->set('groupBy', $request->getByType('groupBy', 1));
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
			$data['coordinates'] = $coordinatesModel->getCoordinatesCustomView();
		}
		if (!$request->isEmpty('cache')) {
			$data['cache'] = $coordinatesModel->readCoordinatesCache();
		}
		if ($request->has('groupBy')) {
			$legend = [];
			foreach (OpenStreetMap_Coordinate_Model::$colors as $key => $value) {
				$legend[] = [
					'value' => \App\Language::translate($key, $sourceModule),
					'color' => $value,
				];
			}
			$data['legend'] = $legend;
		}
		if (!empty($coordinatesCenter)) {
			$data['coordinatesCeneter'] = $coordinatesCenter;
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
