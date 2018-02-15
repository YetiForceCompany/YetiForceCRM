<?php

/**
 * Map Modal Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OpenStreetMap_MapModal_View extends Vtiger_BasicModal_View
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
		if (!$request->isEmpty('srcModule', true) && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('srcModule'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function getSize(\App\Request $request)
	{
		return 'modal-fullscreen';
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		if (!$request->isEmpty('srcModule', true)) {
			$srcModuleModel = Vtiger_Module_Model::getInstance($request->getByType('srcModule'));
			$fields = $srcModuleModel->getFields();
			$fieldsToGroup = [];
			foreach ($fields as $fieldModel) {
				if ($fieldModel->getFieldDataType() === 'picklist') {
					$fieldsToGroup[] = $fieldModel;
				}
			}
			$cacheRecords[$request->getByType('srcModule')] = 0; // default values
			$cacheRecords = array_merge($cacheRecords, $coordinatesModel->getCachedRecords());
		} else {
			$cacheRecords = $coordinatesModel->getCachedRecords();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('ALLOWED_MODULES', $moduleModel->getAllowedModules());
		$viewer->assign('FIELDS_TO_GROUP', $fieldsToGroup);
		$viewer->assign('CACHE_GROUP_RECORDS', $cacheRecords);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SRC_MODULE', $request->getByType('srcModule'));
		$this->preProcess($request);
		$viewer->view('MapModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(\App\Request $request)
	{
		$jsFileNames = [
			'~libraries/leaflet/dist/leaflet.js',
			'~libraries/leaflet.markercluster/dist/leaflet.markercluster.js',
			'~libraries/leaflet.awesome-markers/dist/leaflet.awesome-markers.js',
			'modules.OpenStreetMap.resources.Map',
		];

		return $this->checkAndConvertJsScripts($jsFileNames);
	}

	public function getModalCss(\App\Request $request)
	{
		$cssFileNames = [
			'~libraries/leaflet/dist/leaflet.css',
			'~libraries/leaflet.markercluster/dist/MarkerCluster.Default.css',
			'~libraries/leaflet.markercluster/dist/MarkerCluster.css',
			'~libraries/leaflet.awesome-markers/dist/leaflet.awesome-markers.css',
		];

		return $this->checkAndConvertCssStyles($cssFileNames);
	}
}
