<?php

/**
 * Map Modal Class
 * @package YetiForce.ModalView
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OpenStreetMap_MapModal_View extends Vtiger_BasicModal_View
{

	public function getSize(\App\Request $request)
	{
		return 'modal-fullscreen';
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		if (!$request->isEmpty('srcModule')) {
			$srcModuleModel = Vtiger_Module_Model::getInstance($request->get('srcModule'));
			$fields = $srcModuleModel->getFields();
			$fieldsToGroup = [];
			foreach ($fields as &$fieldModel) {
				if ($fieldModel->getFieldDataType() == 'picklist') {
					$fieldsToGroup [] = $fieldModel;
				}
			}
			$cacheRecords[$request->get('srcModule')] = 0; // default values
			$cacheRecords = array_merge($cacheRecords, $coordinatesModel->getCachedRecords());
		} else {
			$cacheRecords = $coordinatesModel->getCachedRecords();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('ALLOWED_MODULES', $moduleModel->getAllowedModules());
		$viewer->assign('FIELDS_TO_GROUP', $fieldsToGroup);
		$viewer->assign('CACHE_GROUP_RECORDS', $cacheRecords);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SRC_MODULE', $request->get('srcModule'));
		$this->preProcess($request);
		$viewer->view('MapModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(\App\Request $request)
	{
		$jsFileNames = array(
			'~libraries/leaflet/leaflet.js',
			'~libraries/leaflet/plugins/markercluster/leaflet.markercluster.js',
			'~libraries/leaflet/plugins/awesome-markers/leaflet.awesome-markers.js',
			"modules.OpenStreetMap.resources.Map",
		);
		return $this->checkAndConvertJsScripts($jsFileNames);
	}

	public function getModalCss(\App\Request $request)
	{
		$cssFileNames = [
			'~libraries/leaflet/leaflet.css',
			'~libraries/leaflet/plugins/markercluster/MarkerCluster.Default.css',
			'~libraries/leaflet/plugins/markercluster/MarkerCluster.css',
			'~libraries/leaflet/plugins/awesome-markers/leaflet.awesome-markers.css',
		];
		return $this->checkAndConvertCssStyles($cssFileNames);
	}
}
