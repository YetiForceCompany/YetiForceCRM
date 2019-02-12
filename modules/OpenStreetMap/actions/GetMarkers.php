<?php

/**
 * Action to get markers.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
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
		$coordinatesModel->set('selectedIds', $request->getArray('selected_ids', 'Alnum'));
		$coordinatesModel->set('viewname', $request->getByType('viewname', 'Alnum'));
		$coordinatesModel->set('excludedIds', $request->getArray('excluded_ids', 'Alnum'));
		$coordinatesModel->set('searchKey', $request->getByType('search_key', 'Alnum'));
		$coordinatesModel->set('operator', $request->getByType('operator'));
		$coordinatesModel->set('groupBy', $request->getByType('groupBy', 'Alnum'));
		$coordinatesModel->set('searchValue', $request->get('searchValue'));
		$coordinatesModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $sourceModule, $request->getByType('search_key', 'Alnum'), $request->getByType('operator')));
		$coordinatesModel->set('lon', $request->get('lon'));
		$coordinatesModel->set('lat', $request->get('lat'));
		$coordinatesModel->set('cache', $request->get('cache'));
		$coordinatesModel->set('search_params', App\Condition::validSearchParams($sourceModule, $request->getArray('search_params')));
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
		if (!empty($coordinatesCenter) || !$request->isEmpty('searchValue')) {
			if (empty($coordinatesCenter['lat']) && empty($coordinatesCenter['lon'])) {
				$coordinatesCenter = ['error' => \App\Language::translate('LBL_NOT_FOUND_PLACE', 'OpenStreetMap')];
			}
			$data['coordinatesCeneter'] = $coordinatesCenter;
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
