<?php

/**
 * Map Modal Class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OpenStreetMap_MapModal_View extends Vtiger_BasicModal_View
{

	public function getSize(Vtiger_Request $request)
	{
		return 'modal-fullscreen';
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$srcModuleModel = Vtiger_Module_Model::getInstance($request->get('srcModule'));
		$this->preProcess($request);
		$fields = $srcModuleModel->getFields();
		$fieldsToGroup = [];
		foreach ($fields as &$fieldModel) {
			if ($fieldModel->getFieldDataType() == 'picklist') {
				$fieldsToGroup [] = $fieldModel;
			}
		}
		$cacheRecords[$request->get('srcModule')] = 0; // default values
		$cacheRecords = array_merge($cacheRecords, OpenStreetMap_Module_Model::getCachedRecords());
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS_TO_GROUP', $fieldsToGroup);
		$viewer->assign('CACHE_GROUP_RECORDS', $cacheRecords);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SRC_MODULE', $request->get('srcModule'));
		$viewer->view('MapModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(Vtiger_Request $request)
	{
		$jsFileNames = array(
			'~libraries/leaflet/leaflet.js',
			'~libraries/leaflet/plugins/markercluster/leaflet.markercluster.js',
			'~libraries/leaflet/plugins/awesome-markers/leaflet.awesome-markers.js',
			"modules.OpenStreetMap.resources.Map",
		);
		return $this->checkAndConvertJsScripts($jsFileNames);
	}

	public function getModalCss(Vtiger_Request $request)
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
